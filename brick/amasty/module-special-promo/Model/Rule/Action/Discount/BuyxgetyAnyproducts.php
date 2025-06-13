<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action 'Buy X get Y free (any products)'.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_XY_ANY_PRODUCTS
 */
class BuyxgetyAnyproducts extends AbstractRule
{
    public const AMASTY_RULE = 'amrules_rule';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty): Data
    {
        $this->beforeCalculate($rule);
        $discountData = $this->_calculate($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    protected function _calculate(Rule $rule, AbstractItem $item)
    {
        $discountData = $this->discountFactory->create();
        $amrule = $rule->getData(self::AMASTY_RULE);

        $allItems = $this->getSortedItems($item->getAddress(), $rule, $amrule->getApplyDiscountTo());

        $productsWithoutDiscountQty = (int)$rule->getDiscountStep();
        $productsWithDiscountQty = (int)$amrule->getNqty();
        $allItemsQty = count($allItems);
        $ruleStep = $productsWithoutDiscountQty + $productsWithDiscountQty;
        if ($allItemsQty < $ruleStep) {
            return $discountData;
        }

        $discountQty = intdiv($allItemsQty, $ruleStep);
        $maxDiscountQty = (int)$rule->getDiscountQty();
        if ($maxDiscountQty) {
            $discountQty = min($discountQty, $maxDiscountQty);
        }

        $itemsId = $this->getItemsId($allItems);
        $itemsForDiscount = array_slice($allItems, 0, $productsWithDiscountQty * $discountQty);
        $rulePercent = min(100, (float)$rule->getDiscountAmount()) / 100;

        $itemAmruleId = $this->getItemAmRuleId($item);

        if (in_array($itemAmruleId, $itemsId, true)) {
            foreach ($itemsForDiscount as $itemForDiscount) {
                if ($itemForDiscount->getAmrulesId() === $itemAmruleId) {
                    $discountData = $this->calculateDiscountForItem($discountData, $item, $rulePercent);
                }
            }
        }

        return $discountData;
    }

    private function calculateDiscountForItem(Data $discountData, AbstractItem $item, float $rulePercent): Data
    {
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

        $parentItem = $item->getParentItem();
        if ($parentItem && $parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
            $qty = $item->getQty();
            $discountAmount *= $qty;
            $baseDiscountAmount *= $qty;
            $itemOriginalPrice *= $qty;
            $baseItemOriginalPrice *= $qty;
        }

        $discountData->setAmount($discountData->getAmount() + $discountAmount);
        $discountData->setBaseAmount($discountData->getBaseAmount() + $baseDiscountAmount);
        $discountData->setOriginalAmount($discountData->getOriginalAmount() + $itemOriginalPrice * $rulePercent);
        $discountData->setBaseOriginalAmount(
            $discountData->getBaseOriginalAmount() + $baseItemOriginalPrice * $rulePercent
        );

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     */
    public function skip($rule, $item): bool
    {
        $parent = $item->getParentItem();

        if ($parent && $parent->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
            return true;
        }

        return parent::skip($rule, $item);
    }
}
