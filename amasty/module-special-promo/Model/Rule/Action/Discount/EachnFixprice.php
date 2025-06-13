<?php
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
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_EACH_N_FIXED
 */
class EachnFixprice extends Eachn
{
    /**
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems(
            $item->getAddress(),
            $rule,
            $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER)
        );

        if ($rule->getAmrulesRule()->getUseFor() == self::USE_FOR_SAME_PRODUCT) {
            $allItems = $this->reduceItems($allItems, $rule);
        }

        $allItems = $this->skipEachN($allItems, $rule);
        $itemsId = $this->getItemsId($allItems);

        $iter = 0;
        /** @var AbstractItem $allItem */
        foreach ($allItems as $allItem) {
            if ($allItem->getAmrulesId() !== $this->getItemAmRuleId($item)) {
                continue;
            }

            $itemQty = $this->getItemQtyToDiscount($item, $itemsId);

            if ($itemQty <= 0) {
                continue;
            }

            $itemPrice = $this->itemPrice->getItemPrice($item);
            $baseItemPrice = $this->itemPrice->getItemBasePrice($item);
            $itemBaseOriginalPrice = $this->itemPrice->getItemBaseOriginalPrice($item);
            $itemOriginalPrice = $this->itemPrice->getItemOriginalPrice($item);
            $rulePrice = $rule->getDiscountAmount();

            $parentItem = $item->getParentItem();

            if ($parentItem && $parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                // We should process calculation once if added to cart bundle product (the same product) qty > 1.
                $iter++;
                if ($iter > 1) {
                    continue;
                }

                $ratio = $this->getBundleDiscountCoefficientForFixPrice($rule, $parentItem);
                $baseAmount = $baseItemPrice * $ratio;
                $quoteAmount = $itemPrice * $ratio;
                $baseOriginalAmount = $itemBaseOriginalPrice * $ratio;
                $originalAmount = $itemOriginalPrice * $ratio;
                $discountAmount = $parentItem->getPrice() - $rulePrice;

                $this->setDiscountData(
                    $discountData,
                    $itemQty,
                    $quoteAmount,
                    $baseAmount,
                    $originalAmount,
                    $baseOriginalAmount
                );
                // rounding
                $this->discountStorage->resolveDiscountAmount(
                    $discountData,
                    (int)$rule->getId(),
                    $discountAmount * $itemQty,
                    count($parentItem->getChildren())
                );

            } else {
                $baseAmount = $rulePrice > $baseItemPrice ? 0 : $baseItemPrice - $rulePrice;
                $quoteAmount = $this->priceCurrency->convert($rulePrice, $item->getQuote()->getStore());
                $originalAmount = $quoteAmount > $itemOriginalPrice ? 0 : $itemOriginalPrice - $quoteAmount;
                $quoteAmount = $quoteAmount > $itemPrice ? 0 : $itemPrice - $quoteAmount;
                $baseOriginalAmount = $rulePrice > $itemBaseOriginalPrice ? 0 : $itemBaseOriginalPrice - $rulePrice;
                $this->setDiscountData(
                    $discountData,
                    $itemQty,
                    $quoteAmount,
                    $baseAmount,
                    $originalAmount,
                    $baseOriginalAmount
                );
            }
        }

        return $discountData;
    }

    private function setDiscountData(
        Data $discountData,
        float $itemQty,
        float $quoteAmount,
        float $baseAmount,
        float $originalAmount,
        float$baseOriginalAmount
    ): void {
        $discountData->setAmount($itemQty * $quoteAmount);
        $discountData->setBaseAmount($itemQty * $baseAmount);
        $discountData->setOriginalAmount($itemQty * $originalAmount);
        $discountData->setBaseOriginalAmount($itemQty * $baseOriginalAmount);
    }
}
