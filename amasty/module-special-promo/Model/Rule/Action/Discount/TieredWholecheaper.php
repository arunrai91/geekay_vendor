<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\ItemCalculationPrice;
use Magento\Bundle\Model\Product\Type;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_TIERED_WHOLE_CART
 */
class TieredWholecheaper extends AbstractRule
{
    public const RULE_VERSION = '1.0.0';
    public const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
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
     * @return Data
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        $step = (int)$rule->getDiscountStep();
        if ($this->itemPrice->getPriceSelector() === ItemCalculationPrice::ORIGIN_WITH_REVERT) {
            $baseSum = $this->getBaseOriginalSumOfItems($allItems);
            $itemPrice = $this->itemPrice->getItemOriginalPrice($item);
            $itemBasePrice = $this->itemPrice->getItemBaseOriginalPrice($item);
        } else {
            $baseSum = $this->getBaseSumOfItems($allItems);
            $itemPrice = $this->itemPrice->getItemPrice($item);
            $itemBasePrice = $this->itemPrice->getItemBasePrice($item);
        }

        if ($baseSum <= 0.001) {
            return $discountData;
        }
        $timesToApply = floor($baseSum / max(1, $step)); // for ex. 300/100=3(times)
        $discountPercent = $timesToApply * $rule->getDiscountAmount(); // 3*5(%)=15(% of discount for whole cart)

        $discountCoefficient = $discountPercent / self::HUNDRED_PERCENT; // 15(%)/100(%)=0.15(coefficient for item)

        $itemsId = $this->getItemsId($allItems);
        if (in_array((int)$item->getAmrulesId(), $itemsId, true)) {
            $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());

            if ($item->getParentItem() && $item->getParentItem()->getProduct()->getTypeId() == Type::TYPE_CODE) {
                $itemQty *= $item->getParentItem()->getQty();
            }

            $discountData = $this->calculateDiscountByCoefficient(
                $discountData,
                $item,
                $itemPrice,
                $itemBasePrice,
                $discountCoefficient,
                $itemQty
            );
        }

        return $discountData;
    }
}
