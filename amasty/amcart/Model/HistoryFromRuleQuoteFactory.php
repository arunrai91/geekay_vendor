<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib;
use Magento\Framework\Stdlib\DateTime;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Framework\Math\Random;

class HistoryFromRuleQuoteFactory
{
    public const DELIVERY_TIME_NOW = 10;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CouponForHistoryFactory
     */
    private $couponForHistoryFactory;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var RuleFactory
     */
    private $salesRuleFactory;

    /**
     * @var array
     */
    private $sameCouponData = [];

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Random
     */
    private $random;

    public function __construct(
        Stdlib\DateTime $dateTime,
        ConfigProvider $configProvider,
        CouponForHistoryFactory $couponForHistoryFactory,
        HistoryFactory $historyFactory,
        RuleFactory $salesRuleFactory,
        EncryptorInterface $encryptor = null,
        Random $random = null
    ) {
        $this->dateTime = $dateTime;
        $this->configProvider = $configProvider;
        $this->couponForHistoryFactory = $couponForHistoryFactory;
        $this->historyFactory = $historyFactory;
        $this->salesRuleFactory = $salesRuleFactory;
        // OM for backward compatibility
        $this->encryptor = $encryptor ?? ObjectManager::getInstance()->get(EncryptorInterface::class);
        $this->random = $random ?? ObjectManager::getInstance()->get(Random::class);
    }

    public function create(
        RuleQuote $ruleQuote,
        Schedule $schedule,
        Rule $rule,
        $time
    ): History {
        $couponData = $this->getCouponData($schedule, $rule, $ruleQuote);
        $deliveryTime = $this->isDeliverNow($schedule)
            ? self::DELIVERY_TIME_NOW
            : $schedule->getDeliveryTime();

        $history = $this->historyFactory->create();
        $publicKey = urlencode($this->encryptor->hash($this->random->getUniqueHash($ruleQuote->getQuoteId())));
        $history->setData(
            array_merge(
                [
                    History::RULE_QUOTE_ID => $ruleQuote->getRuleQuoteId(),
                    History::SCHEDULE_ID => $schedule->getScheduleId(),
                    History::STATUS => History::STATUS_PROCESSING,
                    History::PUBLIC_KEY => $publicKey,
                    History::SCHEDULED_AT => $this->dateTime->formatDate($time + $deliveryTime),
                    RuleQuote::STORE_ID => $ruleQuote->getStoreId(),
                    RuleQuote::CUSTOMER_EMAIL => $ruleQuote->getCustomerEmail(),
                    RuleQuote::CUSTOMER_FIRSTNAME => $ruleQuote->getCustomerFirstname(),
                    RuleQuote::CUSTOMER_LASTNAME => $ruleQuote->getCustomerLastname(),
                    RuleQuote::CUSTOMER_PHONE => $ruleQuote->getCustomerPhone()
                ],
                $couponData
            )
        );

        return $history;
    }

    private function getCouponData(
        Schedule $schedule,
        Rule $rule,
        RuleQuote $ruleQuote
    ): array {
        $couponData = [];
        $salesCoupon = false;
        $salesRule = false;

        if ($schedule->getSendSameCoupon()) {
            $couponData = $this->sameCouponData;
        } else {
            if ($schedule->getUseShoppingCartRule()) {
                /** @var \Magento\SalesRule\Model\Rule $salesRule */
                $salesRule = $this->salesRuleFactory->create()->load($schedule->getSalesRuleId());

                if ($salesRule->getRuleId()) {
                    $salesCoupon = $this->couponForHistoryFactory->generateCouponPool($salesRule);
                    $salesCoupon->setExpirationDate($salesRule->getToDate());
                }
            } elseif ($schedule->getSimpleAction()) {
                $salesRule = $this->couponForHistoryFactory->create($ruleQuote, $schedule, $rule);
            }

            if ($salesRule) {
                if ($salesCoupon) {
                    $couponData = [
                        'sales_rule_id' => $salesRule->getRuleId(),
                        'sales_rule_coupon_id' => $salesCoupon->getId(),
                        'sales_rule_coupon' => $salesCoupon->getCode(),
                        'sales_rule_coupon_expiration_date' => $salesCoupon->getExpirationDate(),
                    ];
                } else {
                    $couponData = [
                        'sales_rule_id' => $salesRule->getRuleId(),
                        'sales_rule_coupon_id' => null,
                        'sales_rule_coupon' => $salesRule->getCouponCode(),
                        'sales_rule_coupon_expiration_date' => $salesRule->getToDate(),
                    ];
                }

                $this->sameCouponData = $couponData;
            }
        }

        return $couponData;
    }

    private function isDeliverNow(Schedule $schedule): bool
    {
        return ($this->configProvider->isDebugMode() || !$schedule->getDeliveryTime());
    }
}
