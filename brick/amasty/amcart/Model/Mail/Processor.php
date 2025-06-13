<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\Mail;

use Amasty\Acart\Model\HistoryEmailSender;
use Amasty\Acart\Model\ResourceModel\History;
use Amasty\Acart\Model\ResourceModel\History\Collection as HistoryCollection;
use Amasty\Acart\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Amasty\Acart\Model\ResourceModel\RuleQuote\CollectionFactory as RuleQuoteCollectionFactory;
use Amasty\Acart\Model\RuleQuote;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as Date;
use Psr\Log\LoggerInterface;

class Processor
{
    public const RECORDS_BATCH_SIZE = 100;

    /**
     * @var HistoryCollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var RuleQuoteCollectionFactory
     */
    private $ruleQuoteCollectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var HistoryEmailSender
     */
    private $historyEmailSender;

    /**
     * @var History
     */
    private $historyResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HistoryCollectionFactory $historyCollectionFactory,
        RuleQuoteCollectionFactory $ruleQuoteCollectionFactory,
        DateTime $dateTime,
        Date $date,
        HistoryEmailSender $historyEmailSender,
        History $historyResource,
        LoggerInterface $logger
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->ruleQuoteCollectionFactory = $ruleQuoteCollectionFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->historyEmailSender = $historyEmailSender;
        $this->historyResource = $historyResource;
        $this->logger = $logger;
    }

    public function process(): void
    {
        $historyList = $this->getHistoryForSending();
        $this->historyResource->setInProgress($historyList->getColumnValues($historyList->getIdFieldName()));
        $failedRecords = [];

        foreach ($historyList as $history) {
            try {
                $this->historyEmailSender->process($history);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $failedRecords[] = $history->getId();
            }
        }

        if ($failedRecords) {
            $this->historyResource->setFailed($failedRecords);
        }
        $this->completeRuleQuotes();
    }

    private function getHistoryForSending(): HistoryCollection
    {
        $historyCollection = $this->historyCollectionFactory->create();

        $historyCollection->addRuleQuoteData()
            ->addRuleData()
            ->addTimeFilter($this->dateTime->formatDate($this->date->gmtTimestamp()))
            ->addFieldToFilter('ruleQuote.' . RuleQuote::STATUS, RuleQuote::STATUS_PROCESSING);
        $historyCollection->getSelect()->limit(self::RECORDS_BATCH_SIZE);

        return $historyCollection;
    }

    /**
     * Changes RuleQuote status after all emails has been sent.
     */
    private function completeRuleQuotes(): void
    {
        $ruleQuoteCollection = $this->ruleQuoteCollectionFactory->create();
        $ruleQuoteCollection->addCompleteFilter();

        foreach ($ruleQuoteCollection as $ruleQuote) {
            $ruleQuote->complete();
        }
    }
}
