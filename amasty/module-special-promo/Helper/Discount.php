<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Helper;

/**
 * "Max amount of discount" helper.
 * @deplacated
 */
class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    public static $maxDiscount = [];

    /**
     * @var array
     */
    private $processedData = [];

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);

        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\Store\Model\Store $store
     *
     * @param int $itemId
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function setDiscount(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Store\Model\Store $store,
        $itemId
    ) {
        $cachedKey = $itemId . '_' . $discountData->getBaseAmount();
        $ruleId = $rule->getId();

        if (!$rule->getAmrulesRule()->getMaxDiscount()) {
            return $discountData;
        }

        // Already limit applied and have no amount to apply for further quote items
        if (isset(self::$maxDiscount[$ruleId])
            && self::$maxDiscount[$ruleId] <= 0
            && !isset($this->processedData[$ruleId][$cachedKey])
        ) {
            $discountData
                ->setAmount(0)
                ->setBaseAmount(0)
                ->setOriginalAmount(0)
                ->setBaseOriginalAmount(0);

            return $discountData;
        }

        if (!isset(self::$maxDiscount[$ruleId]) || isset($this->processedData[$ruleId][$cachedKey])) {
            self::$maxDiscount[$ruleId] = (float)$rule->getAmrulesRule()->getMaxDiscount();
            $this->processedData[$ruleId] = null;
        }

        if ((self::$maxDiscount[$ruleId] > 0.00)
            && (self::$maxDiscount[$ruleId] - $discountData->getBaseAmount() < 0)
        ) {
            $convertedPrice = $this->priceCurrency->convert(self::$maxDiscount[$ruleId], $store);
            $discountData->setBaseAmount(self::$maxDiscount[$ruleId]);
            $discountData->setAmount($this->priceCurrency->round($convertedPrice));
            $discountData->setBaseOriginalAmount(self::$maxDiscount[$ruleId]);
            $discountData->setOriginalAmount($this->priceCurrency->round($convertedPrice));
            self::$maxDiscount[$ruleId] = 0;
        } else {
            self::$maxDiscount[$ruleId] =
                self::$maxDiscount[$ruleId] - round($discountData->getBaseAmount(), 2);
        }

        $this->processedData[$ruleId][$cachedKey] = true;

        return $discountData;
    }
}
