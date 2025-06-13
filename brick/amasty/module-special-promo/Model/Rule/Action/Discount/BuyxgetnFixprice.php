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
 * @see \Amasty\Rules\Helper\Data::TYPE_XN_FIXED
 */
class BuyxgetnFixprice extends Buyxgety
{
    public const RULE_VERSION = '1.0.0';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     *
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     *
     * @return Data Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();

        // no conditions for Y elements
        if (!$rule->getAmrulesRule()->getPromoCats() && !$rule->getAmrulesRule()->getPromoSkus()) {
            return $discountData;
        }

        $address = $item->getAddress();
        $triggerItems = $this->getTriggerElements($address, $rule);
        $realQty = $this->getTriggerElementQty($triggerItems);
        $maxQty = $this->getNQty($rule, $realQty);
        // find all allowed Y (discounted) elements and calculate total discount
        $passedItems = [];
        $lastId = 0;
        $currQty = 0;
        $allItems = $this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER);
        $itemsId = $this->getItemsId($allItems);

        foreach ($allItems as $allItem) {
            if ($currQty >= $maxQty) {
                break;
            }

            // we always skip child items and calculate discounts inside parents
            if (!$this->canProcessItem($allItem, $triggerItems, $passedItems)) {
                continue;
            }
            // what should we do with bundles when we treat them as
            // separate items
            $passedItems[$allItem->getAmrulesId()] = $allItem->getAmrulesId();

            if (!$this->isDiscountedItem($rule, $allItem)) {
                continue;
            }

            $qty = $this->getItemQty($allItem);

            if (($qty == $currQty) && ($lastId == $this->getItemAmRuleId($item))) {
                continue;
            }

            $qty = min($maxQty - $currQty, $qty);
            $currQty += $qty;

            if (in_array($this->getItemAmRuleId($item), $itemsId)
                && $allItem->getAmrulesId() === $this->getItemAmRuleId($item)
            ) {
                $parentItem = $item->getParentItem();
                $itemQty = $qty;

                if ($parentItem && $parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                    $this->getDiscountForBundleProduct($rule, $parentItem, $item, $itemQty, $discountData);
                } else {
                    $itemPrice = $this->itemPrice->getItemPrice($item);
                    $quoteAmount = $this->priceCurrency->convert(
                        $rule->getDiscountAmount(),
                        $item->getQuote()->getStore()
                    );
                    $quoteAmount = $itemPrice - $quoteAmount;
                    $discountData->setAmount($itemQty * $quoteAmount);
                    $discountData->setBaseAmount($itemQty * $quoteAmount);
                    $discountData->setOriginalAmount($itemQty * $quoteAmount);
                    $discountData->setBaseOriginalAmount($itemQty * $quoteAmount);
                }
            }
        }

        return $discountData;
    }
}
