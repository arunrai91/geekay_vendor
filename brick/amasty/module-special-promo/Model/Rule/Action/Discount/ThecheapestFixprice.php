<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\ItemCalculationPrice;
use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

class ThecheapestFixprice extends Thecheapest
{
    protected function calculateDiscountData(
        AbstractItem $item,
        float $itemQty,
        float $rulePercent,
        Rule $rule
    ): Data {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $discountAmount = $rule->getDiscountAmount();

        $itemPrice = $this->itemPrice->getItemPrice($item);
        $baseItemPrice = $this->itemPrice->getItemBasePrice($item);
        $itemOriginalPrice = $this->itemPrice->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->itemPrice->getItemBaseOriginalPrice($item);
        $parentItem = $item->getParentItem();

        if ($parentItem && ($parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE)) {
            $ratio = $this->getBundleDiscountCoefficientForFixPrice($rule, $parentItem);
            $baseAmount = $baseItemPrice * $ratio;
            $amount = $itemPrice * $ratio;
            $baseOriginalAmount = $baseItemOriginalPrice * $ratio;
            $originalAmount = $itemOriginalPrice * $ratio;
            $discountAmount = max((($parentItem->getPrice() - $discountAmount) * $parentItem->getQty()), 0);
        } else {
            $amount = max($itemPrice - $discountAmount, 0);
            $baseOriginalAmount = max($baseItemOriginalPrice - $discountAmount, 0);
            $baseAmount = max($baseItemPrice - $discountAmount, 0);
            $originalAmount = max($itemOriginalPrice - $discountAmount, 0);

            $amount = $this->itemPrice->resolveFinalPriceRevert($amount, $item);
            $baseAmount = $this->itemPrice->resolveBaseFinalPriceRevert($baseAmount, $item);
        }

        $discountData->setAmount($itemQty * $amount);
        $discountData->setBaseAmount($itemQty * $baseAmount);
        $discountData->setOriginalAmount($itemQty * $originalAmount);
        $discountData->setBaseOriginalAmount($itemQty * $baseOriginalAmount);

        if ($parentItem && ($parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE)) {
            $this->discountStorage->resolveDiscountAmount(
                $discountData,
                (int)$rule->getId(),
                $discountAmount,
                count($parentItem->getChildren())
            );
        }

        return $discountData;
    }

    protected function getBundleDiscountCoefficientForFixPrice(Rule $rule, AbstractItem $item): float
    {
        $baseSum = $this->getBaseSum($item);

        return max((($baseSum - $rule->getDiscountAmount()) / $baseSum), 0);
    }

    private function getBaseSum(AbstractItem $item): float
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
            if (($allItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) && $allItem->isChildrenCalculated()) {
                continue;
            }
            $itemBasePrice = $this->validator->getItemBasePrice($allItem) * $allItem->getQty();

            $baseSum += $itemBasePrice;
        }

        return $baseSum;
    }
}
