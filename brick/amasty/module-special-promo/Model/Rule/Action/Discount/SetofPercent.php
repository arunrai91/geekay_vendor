<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Catalog\Model\Product\Type;
use Magento\SalesRule\Model\Rule as RuleModel;

/**
 * Amasty Rules calculation by action.
 * @see \Amasty\Rules\Helper\Data::TYPE_SETOF_PERCENT
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SetofPercent extends AbstractSetof
{
    /**
     * @param RuleModel $rule
     *
     * @return $this
     */
    protected function calculateDiscountForRule($rule, $item)
    {
        list($setQty, $itemsForSet) = $this->prepareDataForCalculation($rule);

        if (!$itemsForSet) {
            return $this;
        }

        $this->calculateDiscountForItems($rule, $itemsForSet);

        foreach ($itemsForSet as $i => $item) {
            unset(self::$allItems[$i]);
        }

        return $this;
    }

    /**
     * @param RuleModel $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem[] $itemsForSet
     *
     * @return void
     *
     * @throws \Exception
     */
    private function calculateDiscountForItems(RuleModel $rule, array $itemsForSet): void
    {
        $ruleId = $this->getRuleId($rule);

        $itemsForSet = $this->populateItemsForSet($itemsForSet);

        foreach ($itemsForSet as $item) {
            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
            $discountData = $this->discountFactory->create();

            $baseItemPrice = $this->itemPrice->getItemBasePrice($item);
            $baseItemOriginalPrice = $this->itemPrice->getItemBaseOriginalPrice($item);

            $parent = $item->getParentItem();
            if ($parent && $parent->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                $baseItemPrice *= $item->getQty();
                $baseItemOriginalPrice *= $item->getQty();
            }

            $percentage = min(100, $rule->getDiscountAmount()) / 100;
            $baseDiscount = $baseItemPrice * $percentage;
            $itemDiscount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());
            $baseOriginalDiscount = $baseItemOriginalPrice * $percentage;
            $originalDiscount = $this->priceCurrency->convert($baseOriginalDiscount, $item->getQuote()->getStore());

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
    }
}
