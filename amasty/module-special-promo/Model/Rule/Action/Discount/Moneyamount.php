<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_AMOUNT
 */
class Moneyamount extends AbstractRule
{
    public const RULE_VERSION = '1.0.0';
    public const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var int
     */
    protected $baseDiscountAmount = 0;

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @param Rule $rule
     * @param AbstractItem $item
     *
     * @return Data Data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        $step = (int)$rule->getDiscountStep();
        $baseSum = $this->getBaseSumOfItems($allItems);
        $timesToApply = floor($baseSum / max(1, $step));
        $maxTimesToApply = max(0, (int)$rule->getDiscountQty()); // remove negative values if any

        if ($maxTimesToApply) {
            $timesToApply = min($timesToApply, $maxTimesToApply);
        }

        $discountAmount = $timesToApply * $rule->getDiscountAmount();
        $this->baseDiscountAmount = $discountAmount;

        if ($discountAmount <= 0.001) {
            return $discountData;
        }

        $discountCoefficient = $discountAmount / $baseSum; // for ex. 4/50=0.08(coefficient for item)
        $itemsId = $this->getItemsId($allItems);
        $itemQty = $this->getItemQtyToDiscount($item, $itemsId);

        if ($itemQty > 0) {
            $discountData = $this->calculateDiscountData($discountData, $item, $discountCoefficient, $itemQty);
        }

        $this->discountStorage->resolveDiscountAmount(
            $discountData,
            (int)$rule->getId(),
            (float)$this->baseDiscountAmount,
            $this->getItemsQty($this->getAllItems($item->getAddress()))
        );

        return $discountData;
    }

    private function calculateDiscountData(
        Data $discountData,
        AbstractItem $item,
        float $discountCoefficient,
        float $itemQty
    ): Data {
        $itemPrice = $this->validator->getItemPrice($item);
        $itemBasePrice = $this->validator->getItemBasePrice($item);
        $discountData = $this->calculateDiscountByCoefficient(
            $discountData,
            $item,
            $itemPrice,
            $itemBasePrice,
            $discountCoefficient,
            $itemQty
        );

        return $discountData;
    }

    /**
     * @param AbstractItem[] $allItems
     * @return int
     */
    private function getItemsQty(array $allItems): int
    {
        $qty = 0;

        foreach ($allItems as $item) {
            $parentItem = $item->getParentItem();
            if (($parentItem && ($parentItem->getProduct()->getTypeId() === Configurable::TYPE_CODE))
                || (($item->getProduct()->getTypeId() === Type::TYPE_BUNDLE) && $item->isChildrenCalculated())
            ) {
                continue;
            }
            $qty++;
        }

        return $qty;
    }
}
