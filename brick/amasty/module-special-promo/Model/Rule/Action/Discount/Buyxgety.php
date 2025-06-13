<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\ItemCalculationPrice;
use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Base class for buyXgetY action group.
 *
 * @see \Amasty\Rules\Helper\Data::BUY_X_GET_Y
 */
abstract class Buyxgety extends AbstractRule
{
    public const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var array
     */
    protected $passedItems = [];

    /**
     * @param Address $address
     * @param Rule $rule
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTriggerElements($address, $rule)
    {
        // find all X (trigger) elements
        $triggerItems = [];
        foreach ($this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER) as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if (!$item->getAmrulesId()) {
                continue;
            }

            $promoSku = $this->rulesDataHelper->getRuleSkus($rule);
            $itemSku = ($item->getProduct()->getTypeId() === Type::TYPE_BUNDLE)
                ? $item->getProduct()->getData('sku')
                : $item->getSku();
            if (!empty($promoSku) && in_array($itemSku, $promoSku)) {
                continue;
            }

            $promoCats = $this->rulesDataHelper->getRuleCats($rule);
            $itemCats = $item->getCategoryIds() ?: $item->getProduct()->getCategoryIds();
            if (!empty($promoCats) && array_intersect($promoCats, $itemCats)) {
                continue;
            }

            $triggerItems[$item->getAmrulesId()] = $item;
        }

        return $triggerItems;
    }

    /**
     * @param array $triggerItems
     *
     * @return int
     */
    public function getTriggerElementQty($triggerItems)
    {
        $realQty = 0;

        /** @var AbstractItem $item */
        foreach ($triggerItems as $item) {
            $realQty += $this->getItemQty($item);
        }

        return $realQty;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return bool
     */
    public function isDiscountedItem($rule, $item)
    {
        $product = $item->getProduct();
        // for configurable product we need to use the child
        if ($item->getHasChildren() && $item->getProduct()->getTypeId() == 'configurable') {
            foreach ($item->getChildren() as $child) {
                // one iteration only
                $product = $child->getProduct();
            }
        }

        $cats = $this->rulesDataHelper->getRuleCats($rule);
        $sku  = $this->rulesDataHelper->getRuleSkus($rule);

        $currentSku  = $product->getData('sku');
        $currentCats = $product->getCategoryIds();

        $parent = $item->getParentItem();

        if (isset($parent)) {
            $parentType = $parent->getProduct()->getTypeId();
            if ($parentType == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $currentSku  = $parent->getProduct()->getData('sku');
                $currentCats = $parent->getProduct()->getCategoryIds();
            }
        }

        if (!in_array($currentSku, $sku) && !array_intersect($cats, $currentCats)) {
            return false;
        }

        return true;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @return bool
     */
    public function skip($rule, $item)
    {
        $parent = $item->getParentItem();

        if ($parent && $parent->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
            return true;
        }

        return parent::skip($rule, $item);
    }

    /**
     * @param AbstractItem $item
     * @param array $triggerItems
     * @param array $passed
     *
     * @return bool
     */
    public function canProcessItem($item, $triggerItems, $passed)
    {
        if (!$item->getAmrulesId()) {
            return false;
        }
        //do not apply discont on triggers
        if (isset($triggerItems[$item->getAmrulesId()])) {
            return false;
        }

        if (in_array($item->getAmrulesId(), $passed)) {
            return false;
        }

        return true;
    }

    /**
     * @param Rule $rule
     * @param int|float $realQty
     *
     * @return float|int|mixed
     */
    protected function getNQty($rule, $realQty)
    {
        if ($rule->getDiscountStep() > $realQty) {
            return 0;
        } else {
            $step = $rule->getDiscountStep();
            $step = max(1, $step);
            $dataNqty = $rule->getAmrulesRule()->getData('nqty');
            $count = floor($realQty / $step);

            if ($dataNqty) {
                $count *= $dataNqty;
            }

            $discountQty = $rule->getDiscountQty();

            if ($discountQty) {
                $nqty = min($count, $discountQty);
            } else {
                $nqty = $count;
            }

            if ($nqty <= 0) {
                $nqty = 1;
            }

            return $nqty;
        }
    }

    protected function getDiscountForBundleProduct(
        Rule $rule,
        AbstractItem $parentItem,
        AbstractItem $item,
        float $itemQty,
        Data $discountData
    ): void {
        $ratio = $this->getBundleDiscountCoefficient($rule, $parentItem);
        $itemPrice = $this->itemPrice->getItemPrice($item);
        $discountAmount = $this->getDiscountAmount($parentItem, $rule, $itemQty);
        $itemQty *= $item->getQty();

        $discountData->setAmount($itemQty * $itemPrice * $ratio);
        $discountData->setBaseAmount($itemQty * $itemPrice * $ratio);
        $discountData->setOriginalAmount($itemQty * $itemPrice * $ratio);
        $discountData->setBaseOriginalAmount($itemQty * $itemPrice * $ratio);

        $this->discountStorage->resolveDiscountAmount(
            $discountData,
            (int)$rule->getId(),
            $discountAmount,
            count($parentItem->getChildren())
        );
    }

    protected function getDiscountAmount(AbstractItem $parentItem, Rule $rule, float $itemQty): float
    {
        return ($parentItem->getPrice() - $rule->getDiscountAmount()) * $itemQty;
    }

    protected function getBundleDiscountCoefficient(Rule $rule, AbstractItem $item): float
    {
        $baseSum = $this->getBaseSum($item);

        return ($baseSum - $rule->getDiscountAmount()) / $baseSum;
    }

    protected function getBaseSum(AbstractItem $item): float
    {
        if ($this->itemPrice->getPriceSelector() === ItemCalculationPrice::ORIGIN_WITH_REVERT) {
            return $this->getBaseOriginalSumOfItems($item->getChildren());
        }

        return $this->getBaseSumOfItems($item->getChildren());
    }

    /**
     * @param AbstractItem[] $allItems
     * @return float|int
     */
    protected function getBaseSumOfItems(array $allItems)
    {
        $baseSum = 0;
        /** @var AbstractItem $allItem */
        foreach ($allItems as $allItem) {
            $itemBasePrice = $this->validator->getItemBasePrice($allItem) * $allItem->getQty();
            $parentItem = $allItem->getParentItem();

            if ($parentItem && $parentItem->getProduct()->getTypeId() == Type::TYPE_BUNDLE) {
                $itemBasePrice *= $parentItem->getQty();
            }
            $baseSum += $itemBasePrice;
        }

        return $baseSum;
    }

    /**
     * @param AbstractItem[] $allItems
     * @return float|int
     */
    protected function getBaseOriginalSumOfItems(array $allItems)
    {
        $baseSum = 0;
        /** @var AbstractItem $allItem */
        foreach ($allItems as $allItem) {
            $itemBasePrice = $this->validator->getItemBaseOriginalPrice($allItem) * $allItem->getQty();
            $parentItem = $allItem->getParentItem();

            if ($parentItem && $parentItem->getProduct()->getTypeId() == Type::TYPE_BUNDLE) {
                $itemBasePrice *= $parentItem->getQty();
            }
            $baseSum += $itemBasePrice;
        }

        return $baseSum;
    }
}
