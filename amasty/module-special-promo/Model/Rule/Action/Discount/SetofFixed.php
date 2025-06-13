<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule as RuleModel;

/**
 * Amasty Rules calculation by action.
 * @see \Amasty\Rules\Helper\Data::TYPE_SETOF_FIXED
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SetofFixed extends AbstractSetof
{
    /**
     * @param RuleModel $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return $this
     */
    protected function calculateDiscountForRule($rule, $item)
    {
        list($setQty, $itemsForSet) = $this->prepareDataForCalculation($rule);

        if (!$itemsForSet) {
            return $this;
        }

        $totalPrice = $this->getBaseItemsPrice($itemsForSet);
        $quoteAmount = $setQty * $rule->getDiscountAmount();

        if ($totalPrice < $quoteAmount) {
            foreach (self::$allItems as $i => $elem) {
                if ($item->getSku() == $elem->getSku()) {
                    unset(self::$allItems[$i]);
                }
            }

            return $this;
        }

        $this->calculateDiscountForItems($totalPrice, $rule, $itemsForSet, $quoteAmount);

        foreach ($itemsForSet as $i => $item) {
            unset(self::$allItems[$i]);
        }

        return $this;
    }

    /**
     * @param float $totalPrice
     * @param RuleModel $rule
     * @param array $itemsForSet
     * @param float|int $quoteAmount
     *
     * @return void
     *
     * @throws \Exception
     */
    private function calculateDiscountForItems($totalPrice, $rule, $itemsForSet, $quoteAmount)
    {
        $ruleId = $this->getRuleId($rule);
        $itemsForSet = $this->populateItemsForSet($itemsForSet);

        foreach ($itemsForSet as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
            $discountData = $this->discountFactory->create();

            $baseItemPrice = $this->itemPrice->getItemBasePrice($item);
            $baseItemOriginalPrice = $this->itemPrice->getItemBaseOriginalPrice($item);

            $percentage = $baseItemPrice / $totalPrice;
            $baseDiscount = $baseItemPrice - $quoteAmount * $percentage;
            $itemDiscount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());
            $baseOriginalDiscount = $baseItemOriginalPrice - $quoteAmount * $percentage;
            $originalDiscount = ($baseItemOriginalPrice / $baseItemPrice) *
                $this->priceCurrency->convert($baseOriginalDiscount, $item->getQuote()->getStore());

            $productId = $this->getUniqueProductIdentifier($item);
            if (!isset(self::$cachedDiscount[$ruleId][$productId])) {
                $discountData->setAmount($itemDiscount);
                $discountData->setBaseAmount($baseDiscount);
                $discountData->setOriginalAmount($originalDiscount);
                $discountData->setBaseOriginalAmount($baseOriginalDiscount);
            } else {
                /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $cachedItem */
                $cachedItem = self::$cachedDiscount[$ruleId][$productId];
                $discountData->setAmount($itemDiscount + $cachedItem->getAmount());
                $discountData->setBaseAmount($baseDiscount + $cachedItem->getBaseAmount());
                $discountData->setOriginalAmount($originalDiscount + $cachedItem->getOriginalAmount());
                $discountData->setBaseOriginalAmount($baseOriginalDiscount + $cachedItem->getBaseOriginalAmount());
            }

            self::$cachedDiscount[$ruleId][$productId] = $discountData;
        }

        $discountAmount = $totalPrice - $quoteAmount;
        $this->resolveDiscountAmount((int)$ruleId, $discountAmount);
    }

    /**
     * Fix discount which become incorrect due to rounding to 2 decimal places.
     */
    private function resolveDiscountAmount(int $ruleId, float $discountAmount): void
    {
        $realDiscountAmount = 0;
        foreach (self::$cachedDiscount[$ruleId] as $productId => $discountData) {
            $realDiscountAmount += round($discountData->getAmount(), 2);
        }

        if ($realDiscountAmount > 0) {
            $diff = round($discountAmount - $realDiscountAmount, 2);

            if ($diff !== 0.00) {
                $discountData->setAmount($discountData->getAmount() + $diff);
                $discountData->setBaseAmount($discountData->getBaseAmount() + $diff);
            }

            self::$cachedDiscount[$ruleId][$productId] = $discountData;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem[] $items
     *
     * @return float
     */
    private function getBaseItemsPrice($items)
    {
        $totalPrice = 0;

        foreach ($items as $item) {
            $totalPrice += $this->validator->getItemBasePrice($item);
        }

        return $totalPrice;
    }
}
