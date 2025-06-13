<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\SalesRule\Model\Quote\Discount;

use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetPacksForCartItem;
use Amasty\Mostviewed\Model\Pack\QuoteItemProcessor;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Quote\Discount as SalesRuleDiscountCollector;
use Magento\SalesRule\Model\Validator;

class ApplyBundlePackDiscount
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var QuoteItemProcessor
     */
    private $quoteItemProcessor;

    /**
     * @var GetPacksForCartItem
     */
    private $getPacksForCartItem;

    /**
     * @var array
     */
    private $appliedRuleIdsAggregate = [];

    public function __construct(
        Validator $validator,
        QuoteItemProcessor $quoteItemProcessor,
        GetPacksForCartItem $getPacksForCartItem
    ) {
        $this->validator = $validator;
        $this->quoteItemProcessor = $quoteItemProcessor;
        $this->getPacksForCartItem = $getPacksForCartItem;
        $this->quoteItemProcessor->clearAppliedPackIds();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollect(
        SalesRuleDiscountCollector $subject,
        SalesRuleDiscountCollector $result,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): SalesRuleDiscountCollector {
        $address = $shippingAssignment->getShipping()->getAddress();

        $items = $shippingAssignment->getItems();
        if (!$items) {
            return $result;
        }

        foreach ($items as $item) {
            if ($this->quoteItemProcessor->isNotApplicableForItem($item)) {
                continue;
            }
            if ($quote->getIsMultiShipping() && $item->getAddress()->getId() !== $address->getId()) {
                continue;
            }
            if ($item->getNoDiscount() || !$this->validator->canApplyDiscount($item) || $item->getParentItem()) {
                continue;
            }

            foreach ($this->castIdsToArray($item->getAppliedRuleIds()) as $appliedRuleId) {
                $this->appliedRuleIdsAggregate[$appliedRuleId][$item->getId()] = $item->getId();
            }

            $this->processItem($total, $item, $result->getCode());
        }

        $discountDescriptionArray = $address->getDiscountDescriptionArray();
        foreach ($this->appliedRuleIdsAggregate as $ruleId => $ruleIdAggregate) {
            if (empty($ruleIdAggregate)) {
                $quote->setAppliedRuleIds($this->removeRuleId($quote->getAppliedRuleIds(), $ruleId));
                $address->setAppliedRuleIds($this->removeRuleId($address->getAppliedRuleIds(), $ruleId));
                unset($discountDescriptionArray[$ruleId]);
            }
        }
        $address->setDiscountDescriptionArray($discountDescriptionArray);
        $this->validator->prepareDescription($address);

        $total->setDiscountDescription($address->getDiscountDescription());
        $total->setSubtotalWithDiscount($total->getSubtotal() + $total->getDiscountAmount());
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $total->getBaseDiscountAmount());
        $address->setDiscountAmount($total->getDiscountAmount());
        $address->setBaseDiscountAmount($total->getBaseDiscountAmount());

        return $result;
    }

    private function processItem(Total $total, AbstractItem $item, string $discountTotalCode): void
    {
        $origDiscountAmount = $item->getDiscountAmount();
        $origBaseDiscountAmount = $item->getBaseDiscountAmount();

        if ($this->applyPacks($item)) {
            // revert cart price rule discount
            $total->addTotalAmount($discountTotalCode, $origDiscountAmount);
            $total->addBaseTotalAmount($discountTotalCode, $origBaseDiscountAmount);

            // add bundle pack discount
            $total->addTotalAmount($discountTotalCode, -$item->getDiscountAmount());
            $total->addBaseTotalAmount($discountTotalCode, -$item->getBaseDiscountAmount());

            foreach ($this->castIdsToArray($item->getAppliedRuleIds()) as $appliedRuleId) {
                unset($this->appliedRuleIdsAggregate[$appliedRuleId][$item->getId()]);
            }
            $item->setAppliedRuleIds(null);

            if ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getDiscounts()) {
                $item->getExtensionAttributes()->setDiscounts([]);
            }
        }
    }

    private function applyPacks(AbstractItem $item): bool
    {
        $bundlePackDiscountApplied = false;

        $this->quoteItemProcessor->clearItemDiscount($item);
        $this->quoteItemProcessor->setItemData([
            'itemPrice' => $this->validator->getItemPrice($item),
            'baseItemPrice' => $this->validator->getItemBasePrice($item),
            'itemOriginalPrice' => $this->validator->getItemOriginalPrice($item),
            'baseOriginalPrice' => $this->validator->getItemBaseOriginalPrice($item)
        ]);

        $appliedPacks = $this->getPacksForCartItem->execute($item);
        foreach ($appliedPacks as $appliedPack) {
            if ($this->quoteItemProcessor->isPackCanBeApplied($appliedPack, $item)) {
                $this->quoteItemProcessor->applyPackRule($appliedPack, $item);
                $this->quoteItemProcessor->saveAppliedPackId($appliedPack->getComplexPack()->getPackId());
            }
        }

        if ($appliedPacks) {
            $bundlePackDiscountApplied = $this->quoteItemProcessor->updateItemDiscountWithPackDiscount($item);
        }

        return $bundlePackDiscountApplied;
    }

    /**
     * @param array|string|null $ruleIds
     * @param int $ruleId
     */
    private function removeRuleId($ruleIds, int $ruleId): string
    {
        $ruleIds = $this->castIdsToArray($ruleIds);
        $key = array_search($ruleId, $ruleIds);
        if ($key !== false) {
            unset($ruleIds[$key]);
        }

        return implode(',', $ruleIds);
    }

    /**
     * @param array|string|null $ids
     */
    private function castIdsToArray($ids): array
    {
        if ($ids && !is_array($ids)) {
            $ids = explode(',', (string)$ids);
        }
        return $ids ?: [];
    }
}
