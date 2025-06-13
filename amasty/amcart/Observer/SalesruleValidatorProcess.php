<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Observer;

use Amasty\Acart\Api\HistoryRepositoryInterface;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NotFoundException;

class SalesruleValidatorProcess implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HistoryRepositoryInterface $historyRepository,
        RuleQuoteRepositoryInterface $ruleQuoteRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->historyRepository = $historyRepository;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
    }

    public function execute(EventObserver $observer): void
    {
        if ($this->scopeConfig->getValue('amasty_acart/general/customer_coupon')) {
            $quote = $observer->getEvent()->getQuote();
            $couponCode = $quote->getCouponCode();

            if ($couponCode) {
                $salesRule = $observer->getEvent()->getRule();

                try {
                    $history = $this->historyRepository->getByCouponCode(
                        $couponCode
                    );

                    if ($history->getSalesRuleId() !== $salesRule->getId()) {
                        $history  = null;
                    }
                } catch (NotFoundException $e) {
                    $history = null;
                }

                if ($history) {
                    try {
                        $ruleQuote = $this->ruleQuoteRepository->getById(
                            (int)$history->getRuleQuoteId()
                        );
                    } catch (NotFoundException $e) {
                        $ruleQuote = null;
                    }

                    if ($ruleQuote) {
                        $customerEmail = $ruleQuote->getCustomerId()
                            ? $quote->getCustomer()->getEmail()
                            : $quote->getBillingAddress()->getEmail();

                        if ((int)$ruleQuote->getQuoteId() !== (int)$quote->getId()
                            && $customerEmail !== $ruleQuote->getCustomerEmail()
                        ) {
                            $quote->setCouponCode('');
                        }
                    }
                }
            }
        }
    }
}
