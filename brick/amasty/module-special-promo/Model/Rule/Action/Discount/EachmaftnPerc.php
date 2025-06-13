<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_EACH_M_AFT_N_PERC
 */
class EachmaftnPerc extends Eachn
{
    public const RULE_VERSION = '1.0.0';
    public const DEFAULT_SORT_ORDER = 'desc';

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
        $rulePercent = min(100, $rule->getDiscountAmount());
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $specialRule = $this->ruleResolver->getSpecialPromotions($rule);
        $allItems = $this->getSortedItems(
            $item->getAddress(),
            $rule,
            $this->getSortOrder($rule, self::DEFAULT_SORT_ORDER)
        );

        $rulePercent /=  100;
        $qty = max(0, $rule->getDiscountQty()); // qty should be positive

        if ($qty) {
            $qty = min($qty, count($allItems));
        } else {
            $qty = count($allItems);
        }

        $offset = (int)$specialRule->getEachm();

        if ($offset < 0) {
            $offset = 0;
        }

        $offset = min($offset, count($allItems));
        $allItems = array_slice($allItems, $offset);
        $allItems = $this->skipEachN($allItems, $rule);
        $itemsId = $this->getItemsId($allItems);

        /** @var AbstractItem $allItem */
        foreach ($allItems as $allItem) {
            if ($this->isContinueEachmaftnCalculation($item, $itemsId, $allItem, $qty)) {
                $itemOriginalPrice = $this->itemPrice->getItemOriginalPrice($item);
                $baseItemOriginalPrice = $this->itemPrice->getItemBaseOriginalPrice($item);

                $itemQty = $this->getItemQtyToDiscount($item, $itemsId);

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

                if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $qty) {
                    $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent * 100);
                    $item->setDiscountPercent($discountPercent);
                }
                $qty--;
            }
        }

        return $discountData;
    }
}
