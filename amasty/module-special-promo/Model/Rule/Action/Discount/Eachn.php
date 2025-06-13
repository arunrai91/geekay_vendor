<?php
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

/**
 * Base class for 'eachN; action group.
 * \Amasty\Rules\Helper\Data::TYPE_EACH_M_AFT_N and others.
 */
abstract class Eachn extends AbstractRule
{
    public const RULE_VERSION = '1.0.0';
    public const DEFAULT_SORT_ORDER = 'desc';
    public const USE_FOR_SAME_PRODUCT = 1;

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
     * @codingStandardsIgnoreStart
     *
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data
     */
    abstract protected function _calculate($rule, $item);
    //@codingStandardsIgnoreEnd

    /**
     * @param array $allItems
     * @param Rule $rule
     *
     * @return array
     */
    public function reduceItems($allItems, $rule)
    {
        $discountStep = (int)$rule->getDiscountStep();
        $step = $discountStep !== '' ? $discountStep : (int)$rule->getAmrulesRule()->getEachm();

        if ($step <= 0) {
            $step = 1;
        }

        $groupedItems = $this->groupItemsBySku($allItems);
        $reducedItems = [];
        foreach ($groupedItems as $group) {
            //@codingStandardsIgnoreStart
            $count = count($group);
            //@codingStandardsIgnoreEnd
            foreach (array_slice($group, $count % $step) as $reducedItem) {
                $reducedItems[] = $reducedItem;
            }
        }

        return $reducedItems;
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
     *
     * @return array
     */
    private function groupItemsBySku($allItems)
    {
        $groupedItems = [];
        foreach ($allItems as $item) {
            $groupedItems[$item->getSku()][] = $item;
        }

        return $groupedItems;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float|null $itemQty
     * @return float
     */
    protected function getBundleDiscountCoefficientForFixDiscount(
        Rule $rule,
        AbstractItem $item,
        float $itemQty = null
    ): float {
        $baseSum = $this->getBaseSum($item);
        if (null === $itemQty) {
            $itemQty = $item->getQty();
        }

        return ($rule->getDiscountAmount() / $baseSum) * $itemQty;
    }

    protected function getBundleDiscountCoefficientForFixDiscountAfterN(
        Rule $rule,
        AbstractItem $item
    ): float {
        return ($rule->getDiscountAmount() / $this->getBaseSum($item));
    }

    protected function getBundleDiscountCoefficientForFixPrice(Rule $rule, AbstractItem $item): float
    {
        $baseSum = $this->getBaseSum($item);

        return ($baseSum - $rule->getDiscountAmount()) / $baseSum;
    }

    private function getBaseSum(AbstractItem $item): float
    {
        if ($this->itemPrice->getPriceSelector() === ItemCalculationPrice::ORIGIN_WITH_REVERT) {
            return $this->getBaseOriginalSumOfItems($item->getChildren());
        }

        return $this->getBaseSumOfItems($item->getChildren());
    }

    /**
     * @param AbstractItem $item
     * @param int[] $itemsId
     * @param AbstractItem $allItem
     * @param int|float $qty
     *
     * @return bool
     */
    protected function isContinueEachmaftnCalculation(
        AbstractItem $item,
        array $itemsId,
        AbstractItem $allItem,
        $qty
    ): bool {
        return (in_array($item->getAmrulesId(), $itemsId) || $this->isValidChildOfBundle($item, $itemsId))
            && $this->isValidItemForEachmaftn($item, $allItem->getAmrulesId())
            && $qty > 0;
    }

    private function isValidItemForEachmaftn(AbstractItem $item, int $ruleId): bool
    {
        $parentItem = $item->getParentItem();
        if ($parentItem
            && ($parentItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE)
        ) {
            return $parentItem->getAmrulesId() === $ruleId;
        }

        return $item->getAmrulesId() === $ruleId;
    }
}
