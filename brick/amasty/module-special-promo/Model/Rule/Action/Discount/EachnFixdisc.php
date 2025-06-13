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
 * @see \Amasty\Rules\Helper\Data::TYPE_EACH_N_FIXDISC
 */
class EachnFixdisc extends Eachn
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

            $baseAmount = $rule->getDiscountAmount();
            $quoteAmount = $this->priceCurrency->convert($baseAmount, $item->getQuote()->getStore());

            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                // We should process calculation once if added to cart bundle product (the same product) qty > 1.
                $iter++;
                if ($iter > 1) {
                    continue;
                }
                $ratio = $this->getBundleDiscountCoefficientForFixDiscount($rule, $parentItem);
                $baseAmount = $ratio * $this->validator->getItemBasePrice($item);
                $quoteAmount = $ratio * $this->validator->getItemPrice($item);

                $this->setDiscountData($discountData, $itemQty, $quoteAmount, $baseAmount);
                // rounding
                $this->discountStorage->resolveDiscountAmount(
                    $discountData,
                    (int)$rule->getId(),
                    (float)$rule->getDiscountAmount() * $itemQty,
                    count($parentItem->getChildren())
                );
            } else {
                $this->setDiscountData($discountData, $itemQty, $quoteAmount, $baseAmount);
            }
        }

        return $discountData;
    }

    private function setDiscountData(Data $discountData, float $itemQty, float $quoteAmount, float $baseAmount): void
    {
        $discountData->setAmount($itemQty * $quoteAmount);
        $discountData->setBaseAmount($itemQty * $baseAmount);
        $discountData->setOriginalAmount($itemQty * $quoteAmount);
        $discountData->setBaseOriginalAmount($itemQty * $baseAmount);
    }
}
