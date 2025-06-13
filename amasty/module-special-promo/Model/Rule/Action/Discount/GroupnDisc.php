<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_GROUP_N_DISC
 */
class GroupnDisc extends AbstractRule
{
    public const RULE_VERSION = '1.0.0';
    public const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     *
     * @throws \Exception
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);
        $discountData = $this->_calculate($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
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
     * @param AbstractItem[] $allItems
     * @param Rule $rule
     * @param int $step
     * @param int $qty
     * @return AbstractItem[]
     */
    private function populateItemsForSet(array $allItems, Rule $rule, int $step, int $qty): array
    {
        $currQty = 0;
        $maxItemsQty = count($allItems) - (count($allItems) % $step);
        foreach ($allItems as $key => $allItem) {
            if ($this->skipBySteps($rule, $step, $key, $currQty, $qty) || $key >= $maxItemsQty) {
                unset($allItems[$key]);
            }
            ++$currQty;
        }

        return $allItems;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems(
            $item->getAddress(),
            $rule,
            $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER)
        );

        $qty = $this->ruleQuantity(count($allItems), $rule);

        if (!$this->hasDiscountItems($allItems, $qty)) {
            return $discountData;
        }

        $step = (int)$rule->getDiscountStep();
        $rulePercent = min(100, $rule->getDiscountAmount()) / 100.0;

        if ($step === 0) {
            $step = 1;
        }

        $allItems = $this->populateItemsForSet($allItems, $rule, $step, $qty);
        $itemsId = $this->getItemsId($allItems);

        foreach ($allItems as $i => $allItem) {
            if ($allItem->getAmrulesId() !== $this->getItemAmRuleId($item)) {
                continue;
            }
            $itemQty = $this->getItemQtyToDiscount($item, $itemsId);

            if ($itemQty <= 0) {
                continue;
            }

            $itemOriginalPrice = $this->itemPrice->getItemOriginalPrice($item);
            $baseItemOriginalPrice = $this->itemPrice->getItemBaseOriginalPrice($item);

            $discountAmount = $this->itemPrice->getItemPrice($item) * $rulePercent;
            $baseDiscountAmount = $this->itemPrice->getItemBasePrice($item) * $rulePercent;

            $discountAmount = $this->itemPrice->resolveFinalPriceRevert(
                $discountAmount,
                $item
            );
            $baseDiscountAmount = $this->itemPrice->resolveBaseFinalPriceRevert(
                $baseDiscountAmount,
                $item
            );

            $discountData->setAmount($itemQty * $discountAmount);
            $discountData->setBaseAmount($itemQty * $baseDiscountAmount);
            $discountData->setOriginalAmount($itemQty * $itemOriginalPrice * $rulePercent);
            $discountData->setBaseOriginalAmount($itemQty * $baseItemOriginalPrice * $rulePercent);
        }

        return $discountData;
    }
}
