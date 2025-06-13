<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Amasty\Acart\Model\Quote\Extension\Handlers\ReadHandler;
use Amasty\Acart\Model\ResourceModel\Quote\Collection as QuotesCollection;
use Amasty\Acart\Model\ResourceModel\Quote\CollectionFactory;
use Amasty\Acart\Model\ResourceModel\Rule\Collection as RulesCollection;
use Amasty\Acart\Utils\BatchLoader;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

class RuleQuotesProcessor
{
    public const UPDATE_INSTALLATION_FLAG = 'amasty_acart_update_installation';

    /**
     * @var ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var ResourceModel\RuleQuote
     */
    private $ruleQuoteResource;

    /**
     * @var RuleQuoteFromRuleAndQuoteFactory
     */
    private $ruleQuoteFromRuleAndQuoteFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReadHandler
     */
    private $quoteReadHandler;

    /**
     * @var BatchLoader
     */
    private $batchLoader;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    public function __construct(
        CollectionFactory $quoteCollectionFactory,
        ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        ConfigProvider $configProvider,
        DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        TimezoneInterface $timezoneInterface,
        RuleQuoteFromRuleAndQuoteFactory $ruleQuoteFromRuleAndQuoteFactory,
        \Amasty\Acart\Model\ResourceModel\RuleQuote $ruleQuoteResource,
        LoggerInterface $logger,
        ReadHandler $quoteReadHandler,
        BatchLoader $batchLoader,
        EventManagerInterface $eventManager,
        FlagManager $flagManager,
        ?EmailValidator $emailValidator = null
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleQuoteFromRuleAndQuoteFactory = $ruleQuoteFromRuleAndQuoteFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->timezoneInterface = $timezoneInterface;
        $this->configProvider = $configProvider;
        $this->ruleQuoteResource = $ruleQuoteResource;
        $this->logger = $logger;
        $this->quoteReadHandler = $quoteReadHandler;
        $this->batchLoader = $batchLoader;
        $this->eventManager = $eventManager;
        $this->flagManager = $flagManager;
        $this->emailValidator = $emailValidator ?? ObjectManager::getInstance()->get(EmailValidator::class);
    }

    public function prepareRuleQuotes(): void
    {
        $quotesToProcessCollection = $this->prepareQuotesToProcessCollection();
        $activeRulesCollection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter(Rule::IS_ACTIVE, Rule::RULE_ACTIVE)
            ->addOrder(Rule::PRIORITY, Collection::SORT_ORDER_ASC);
        $this->processQuotes($quotesToProcessCollection, $activeRulesCollection);

        $this->deleteAmbiguousRuleQuotes();
    }

    private function prepareQuotesToProcessCollection(): QuotesCollection
    {
        $abandonedGap = $this->configProvider->getAbandonedGap() * 60;
        $installTime = (int)$this->flagManager->getFlagData(self::UPDATE_INSTALLATION_FLAG);

        $quotesToProcessCollection = $this->quoteCollectionFactory->create();
        $quotesToProcessCollection->addAbandonedCartsFilter()
            ->joinQuoteEmail(
                $this->configProvider->isDebugMode(),
                $this->configProvider->getDebugEnabledEmailDomains()
            );

        if (!$this->configProvider->isDebugMode()) {
            $quotesToProcessCollection->addTimeFilter(
                $this->dateTime->formatDate($this->date->gmtTimestamp() - $abandonedGap),
                $this->dateTime->formatDate($installTime - $abandonedGap)
            );
        }

        if ($this->configProvider->isOnlyCustomers()) {
            $quotesToProcessCollection->addFieldToFilter('main_table.customer_id', ['notnull' => true]);
        }

        return $quotesToProcessCollection;
    }

    private function processQuotes(
        QuotesCollection $quotesToProcessCollection,
        RulesCollection $activeRulesCollection
    ): void {
        $processedQuoteIds = $customerIds = [];
        foreach ($this->batchLoader->execute($quotesToProcessCollection) as $quote) {
            foreach ($activeRulesCollection as $rule) {
                try {
                    if (!in_array($quote->getId(), $processedQuoteIds) && $rule->validate($quote)) {
                        $this->quoteReadHandler->read($quote);
                        $customerEmail = $this->resolveEmailFromQuote($quote);

                        if (!empty($customerEmail) && $this->emailValidator->isValid($customerEmail)) {
                            $this->ruleQuoteFromRuleAndQuoteFactory->create($rule, $quote);
                            $processedQuoteIds[] = $quote->getId();
                            $customerIds[] = $quote->getCustomerId();
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    continue 2;
                }
            }
        }
        if (!empty($customerIds)) {
            $this->eventManager->dispatch('amasty_pushnotifications_by_event', [
                'event' => 'amasty_acart_pushnotifications',
                'customer_ids' => array_unique($customerIds)
            ]);
        }
    }

    /**
     * Delete previous rule_quote entities if a setting "send email one time per quote" is disabled.
     */
    private function deleteAmbiguousRuleQuotes(): void
    {
        $this->ruleQuoteResource->deleteNotUnique();
    }

    private function resolveEmailFromQuote(CartInterface $quote): ?string
    {
        $customerEmail = $quote->getCustomerEmail();

        if (!$customerEmail
            && $quote->getExtensionAttributes()
            && $quote->getExtensionAttributes()->getAmAcartQuoteEmail()
            && $quote->getExtensionAttributes()->getAmAcartQuoteEmail()->getCustomerEmail()
        ) {
            $customerEmail = $quote->getExtensionAttributes()->getAmAcartQuoteEmail()->getCustomerEmail();
        }

        return $customerEmail;
    }
}
