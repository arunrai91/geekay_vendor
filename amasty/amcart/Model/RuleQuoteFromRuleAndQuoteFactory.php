<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Amasty\Acart\Api\Data\HistoryDetailInterface;
use Amasty\Acart\Api\Data\HistoryDetailInterfaceFactory;
use Amasty\Acart\Api\Data\HistoryInterface;
use Amasty\Acart\Api\Data\RuleQuoteInterface;
use Amasty\Acart\Api\HistoryRepositoryInterface;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Amasty\Acart\Model\ResourceModel\RuleQuote as RuleQuoteResource;
use Amasty\Acart\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib;
use Magento\Quote\Model\Quote;

class RuleQuoteFromRuleAndQuoteFactory
{
    /**
     * @var Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var RuleQuoteFactory
     */
    private $ruleQuoteFactory;

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var HistoryFromRuleQuoteFactory
     */
    private $historyFromRuleQuoteFactory;

    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @var HistoryDetailInterfaceFactory
     */
    private $detailFactory;

    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var ResourceModel\RuleQuote
     */
    private $ruleQuoteResource;

    public function __construct(
        Stdlib\DateTime\DateTime $date,
        Stdlib\DateTime $dateTime,
        RuleQuoteFactory $ruleQuoteFactory,
        RuleQuoteRepositoryInterface $ruleQuoteRepository,
        HistoryFromRuleQuoteFactory $historyFromRuleQuoteFactory,
        HistoryRepositoryInterface $historyRepository,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        HistoryDetailInterfaceFactory $detailFactory,
        RuleQuoteResource $ruleQuoteResource
    ) {
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->ruleQuoteFactory = $ruleQuoteFactory;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->historyFromRuleQuoteFactory = $historyFromRuleQuoteFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->detailFactory = $detailFactory;
        $this->historyRepository = $historyRepository;
        $this->ruleQuoteResource = $ruleQuoteResource;
    }

    /**
     * @param Rule $rule
     * @param Quote $quote
     * @param bool $testMode
     *
     * @return RuleQuoteInterface
     * @throws LocalizedException
     */
    public function create(
        Rule $rule,
        Quote $quote,
        bool $testMode = false
    ): RuleQuoteInterface {
        $ruleQuote = $this->ruleQuoteFactory->create();
        $customerEmail = $quote->getCustomerEmail();
        if (!$customerEmail
            && $quote->getExtensionAttributes()
            && $quote->getExtensionAttributes()->getAmAcartQuoteEmail()
            && $quote->getExtensionAttributes()->getAmAcartQuoteEmail()->getCustomerEmail()
        ) {
            $customerEmail = $quote->getExtensionAttributes()->getAmAcartQuoteEmail()->getCustomerEmail();
        }

        if ($customerEmail) {
            $time = $this->date->gmtTimestamp();
            $ruleQuote->setData(
                [
                    'rule_id' => $rule->getRuleId(),
                    'quote_id' => $quote->getId(),
                    'store_id' => $quote->getStoreId(),
                    'status' => RuleQuote::STATUS_PROCESSING,
                    'customer_id' => $quote->getCustomerId(),
                    'customer_email' => $customerEmail,
                    'customer_firstname' => $quote->getCustomerFirstname(),
                    'customer_lastname' => $quote->getCustomerLastname(),
                    'customer_phone' => $this->getCustomerTelephone($quote),
                    'test_mode' => $testMode,
                    'created_at' => $this->dateTime->formatDate($time)
                ]
            );

            $histories = [];
            $scheduleCollection = $this->scheduleCollectionFactory->create();
            $scheduleCollection->addFieldToFilter(Schedule::RULE_ID, $rule->getRuleId());
            foreach ($scheduleCollection as $schedule) {
                $history = $this->historyFromRuleQuoteFactory->create($ruleQuote, $schedule, $rule, $time);
                $history->setHistoryDetails($this->prepareHistoryDetails($history, $quote));
                $histories[] = $history;
            }

            if (!$histories) {
                $ruleQuote->setStatus(RuleQuote::STATUS_COMPLETE); //to prevent double processing of that quote
                $this->ruleQuoteRepository->save($ruleQuote);

                throw new LocalizedException(__("Rule do not have any Schedule"));
            }

            $this->performSaveTransaction($ruleQuote, $histories);
            $ruleQuote->setData('assigned_history', $histories);
        }

        return $ruleQuote;
    }

    /**
     * @param HistoryInterface $history
     * @param Quote $quote
     *
     * @return HistoryDetailInterface[]
     */
    private function prepareHistoryDetails(HistoryInterface $history, Quote $quote): array
    {
        $details = [];
        foreach ($quote->getAllItems() as $quoteItem) {
            $detail = $this->detailFactory->create();
            $detail->setProductName((string)$quoteItem->getName());
            $detail->setProductPrice((float)$quoteItem->getPrice());
            $detail->setProductSku((string)$quoteItem->getSku());
            $detail->setProductQty((int)$quoteItem->getQty());
            $detail->setStoreId((int)$quoteItem->getStoreId());
            $detail->setCurrencyCode((string)$quote->getCurrency()->getQuoteCurrencyCode());
            $details[] = $detail;
        }

        return $details;
    }

    private function getCustomerTelephone($quote): ?string
    {
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress->getTelephone()) {
            return $billingAddress->getTelephone();
        }

        return $quote->getShippingAddress()->getTelephone();
    }

    /**
     * @param RuleQuoteInterface $ruleQuote
     * @param HistoryInterface[] $histories
     *
     * @return void
     * @throws \Exception
     */
    private function performSaveTransaction(RuleQuoteInterface $ruleQuote, array $histories): void
    {
        try {
            $this->ruleQuoteResource->beginTransaction();
            $this->ruleQuoteRepository->save($ruleQuote);
            foreach ($histories as $history) {
                $history->setRuleQuoteId($ruleQuote->getRuleQuoteId());
                $this->historyRepository->save($history);
            }
            $this->ruleQuoteResource->commit();
        } catch (\Exception $exception) {
            $this->ruleQuoteResource->rollBack();
            throw $exception;
        }
    }
}
