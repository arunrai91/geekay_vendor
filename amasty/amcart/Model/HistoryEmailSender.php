<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Amasty\Acart\Api\BlacklistRepositoryInterface;
use Amasty\Acart\Api\Data\HistoryInterface;
use Amasty\Acart\Api\HistoryRepositoryInterface;
use Amasty\Acart\Api\RuleQuoteRepositoryInterface;
use Amasty\Acart\Model\Mail\MessageBuilder\MessageBuilder;
use Amasty\Acart\Model\Mail\MessageBuilder\MessageBuilderFactory;
use Amasty\Acart\Model\Mail\TemplateBuilder;
use Amasty\Acart\Model\Mail\TrackingPixelModifier;
use Amasty\Acart\Model\ResourceModel\Inventory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Mail\MessageFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;

/**
 * Moved from \Amasty\Acart\Model\History, consider refactoring
 */
class HistoryEmailSender
{
    public const BCC_METHOD = 'bcc';
    public const COPY_METHOD = 'copy';

    /**
     * @var TemplateBuilder
     */
    private $templateBuilder;

    /**
     * @var DateTime\DateTime
     */
    private $date;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TransportInterfaceFactory
     */
    private $mailTransportFactory;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Collection
     */
    private $newsletterSubscriberCollection;

    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var BlacklistRepositoryInterface
     */
    private $blacklistRepository;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var TrackingPixelModifier
     */
    private $trackingPixelModifier;

    /**
     * @var RuleQuoteRepositoryInterface
     */
    private $ruleQuoteRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var MessageBuilder|null
     */
    private $messageBuilder;

    public function __construct(
        TemplateBuilder $templateBuilder,
        DateTime\DateTime $date,
        DateTime $dateTime,
        TransportInterfaceFactory $mailTransportFactory,
        MessageFactory $messageFactory,
        QuoteFactory $quoteFactory,
        ConfigProvider $configProvider,
        Collection $newsletterSubscriberCollection,
        MessageBuilderFactory $messageBuilderFactory,
        ?UrlManager $urlManager, // deprecated.
        HistoryRepositoryInterface $historyRepository,
        BlacklistRepositoryInterface $blacklistRepository,
        Inventory $inventory,
        TrackingPixelModifier $trackingPixelModifier,
        RuleQuoteRepositoryInterface $ruleQuoteRepository,
        Registry $registry
    ) {
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->messageFactory = $messageFactory;
        $this->quoteFactory = $quoteFactory;
        $this->configProvider = $configProvider;
        $this->newsletterSubscriberCollection = $newsletterSubscriberCollection;
        $this->historyRepository = $historyRepository;
        $this->blacklistRepository = $blacklistRepository;
        $this->inventory = $inventory;
        $this->trackingPixelModifier = $trackingPixelModifier;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->registry = $registry;
        $this->messageBuilder = $messageBuilderFactory->create();
        $this->templateBuilder = $templateBuilder;
    }

    /**
     * @param HistoryInterface|History $history
     * @param bool $testMode
     *
     * @return void
     */
    public function process(
        HistoryInterface $history,
        bool $testMode = false
    ): void {
        if ($this->cancel($history)) {
            $history->setStatus(History::STATUS_CANCEL_EVENT);
            $this->historyRepository->save($history);
            $ruleQuote = $this->ruleQuoteRepository->getById((int)$history->getRuleQuoteId());
            $ruleQuote->complete();

            return;
        }
        $history->setExecutedAt($this->dateTime->formatDate($this->date->gmtTimestamp()));

        if ($testMode) {
            $this->sendEmail($history, $testMode);
            $status = History::STATUS_SENT;
        } else {
            try {
                $blacklist = $this->blacklistRepository->getByCustomerEmail((string)$history->getCustomerEmail());
            } catch (NotFoundException $e) {
                $blacklist = null;
            }

            if ($blacklist && $blacklist->getBlacklistId()) {
                $status = History::STATUS_BLACKLIST;
            } elseif (!$this->validateNewsletterSubscribersOnly(
                (string)$history->getCustomerEmail(),
                (int)$history->getStoreId()
            )) {
                $status = History::STATUS_NOT_NEWSLETTER_SUBSCRIBER;
            } else {
                $this->sendEmail($history, $testMode);
                $status = History::STATUS_SENT;
            }
        }
        $history->setStatus($status);
        $history->setFinishedAt($this->dateTime->formatDate($this->date->gmtTimestamp()));

        $this->historyRepository->save($history);
    }

    /**
     * @param string $email
     * @param int $storeId
     *
     * @return bool
     */
    private function validateNewsletterSubscribersOnly(string $email, int $storeId): bool
    {
        if (!$this->configProvider->isEmailsToNewsletterSubscribersOnly($storeId)) {
            return true;
        }

        /** @var Subscriber|null $newsletterSubscriber */
        $newsletterSubscriber = $this->newsletterSubscriberCollection->getItemByColumnValue(
            'subscriber_email',
            $email
        );

        return $newsletterSubscriber
            && $newsletterSubscriber->getSubscriberStatus() == Subscriber::STATUS_SUBSCRIBED;
    }

    /**
     * @param HistoryInterface|History $history
     *
     * @return bool
     */
    private function cancel(HistoryInterface $history): bool
    {
        if (!$history->getCancelCondition()) {
            return false;
        }

        $cancel = false;
        foreach (explode(',', $history->getCancelCondition()) as $cancelCondition) {
            $quote = $this->quoteFactory->create()->load($history->getQuoteId());

            if (!$quote->getId()) {
                $quote = $quote->loadByIdWithoutStore($history->getQuoteId());
            }

            $quoteValidation = $this->validateCancelQuote($quote);
            switch ($cancelCondition) {
                case Rule::CANCEL_CONDITION_ALL_PRODUCTS_WENT_OUT_OF_STOCK:
                    if (!$quoteValidation['all_products']) {
                        $cancel = true;
                    }
                    break;
                case Rule::CANCEL_CONDITION_ANY_PRODUCT_WENT_OUT_OF_STOCK:
                    if (!$quoteValidation['any_products']) {
                        $cancel = true;
                    }
                    break;
                case Rule::CANCEL_CONDITION_ALL_PRODUCTS_WERE_DISABLED:
                    if ($quoteValidation['all_disabled']) {
                        $cancel = true;
                    }
                    break;
            }
        }

        return $cancel;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    private function validateCancelQuote(Quote $quote): array
    {
        $inStock = 0;

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId()
                && $item->getQuote()
                && !$item->getQuote()->getIsSuperMode()
                && $this->inventory->getStockStatus(
                    $item->getProduct()->getSku(),
                    $item->getProduct()->getStore()->getWebsite()->getCode()
                )
            ) {
                $inStock++;
            }
        }

        return [
            'all_products' => !(($inStock === 0)),
            'any_products' => !(((count($quote->getAllItems()) - $inStock) !== 0)),
            'all_disabled' => count($quote->getAllVisibleItems()) === 0
        ];
    }

    /**
     * @param HistoryInterface|History $history
     * @param bool $testMode
     *
     * @return void
     * @throws LocalizedException
     */
    private function sendEmail(HistoryInterface $history, bool $testMode = false): void
    {
        $storeId = $history->getStoreId();
        $safeMode = $this->configProvider->isSafeMode($storeId);
        $recipientEmail = $this->configProvider->getRecipientEmailForTest();
        // remove spaces in email address in case third party extensions does not filter them.
        $to = str_replace(' ', '', $history->getCustomerEmail());

        if ($testMode || $safeMode) {
            if ($recipientEmail) {
                $to = $recipientEmail;
            } else {
                throw new LocalizedException(
                    __('Please fill in the test email in the extension configuration section')
                );
            }
        }

        if (!$history->getEmailBody()) {
            $template = $this->templateBuilder->build($history);
            $emailBody = $template->processTemplate();
            //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $emailSubject = html_entity_decode((string)$template->getSubject(), ENT_QUOTES);
        } else {
            [$emailBody, $emailSubject] = [$history->getEmailBody(), $history->getEmailSubject()];
        }

        if (is_string($to)) {
            $to = explode(',', $to);
        }

        $copyRecipients = $this->configProvider->getBcc($storeId);
        $copyRecipients = empty($copyRecipients)
            ? []
            : array_map('trim', explode(',', $copyRecipients));
        $emailCopyType = $this->configProvider->getCopyMethod($storeId);

        // Send email to customer. Bcc depends on module configuration.
        $this->sendCustomerEmail($history, $to, $emailBody, $emailSubject, $copyRecipients, $emailCopyType);
        // Send separate email copies.
        if (!$testMode && !$safeMode && $emailCopyType === self::COPY_METHOD && !empty($copyRecipients)) {
            $this->sendSeparateEmail($history, $emailBody, $emailSubject, $copyRecipients);
        }
    }

    private function sendCustomerEmail(
        HistoryInterface $history,
        array $to,
        string $emailBody,
        string $emailSubject,
        array $copyRecipients,
        string $emailCopyType
    ): void {
        if (!empty($copyRecipients) && $emailCopyType === self::BCC_METHOD) {
            $this->createAndSendMessage($history, $to, $emailBody, $emailSubject, $copyRecipients);
        } else {
            $this->createAndSendMessage($history, $to, $emailBody, $emailSubject);
        }
    }

    private function sendSeparateEmail(
        HistoryInterface $history,
        string $emailBody,
        string $emailSubject,
        array $copyRecipients
    ): void {
        if ($this->configProvider->isCustomerContactDetails()) {
            $template = $this->templateBuilder->build($history, true);
            $emailBody = $template->processTemplate();
            //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $emailSubject = html_entity_decode((string)$template->getSubject(), ENT_QUOTES);
        }
        $this->createAndSendMessage(
            $history,
            $copyRecipients,
            $emailBody,
            $emailSubject
        );
    }

    private function createAndSendMessage(
        HistoryInterface $history,
        array $toEmail,
        string $emailBody,
        ?string $emailSubject,
        ?array $bcc = null
    ): void {
        $storeId = $history->getStoreId();
        $senderName = $this->configProvider->getSenderName($storeId);
        $senderEmail = $this->configProvider->getSenderEmail($storeId);
        $replyToEmail = $this->configProvider->getReplyToEmail($storeId);
        // Compatibility with Mageplaza_Smtp
        $isSetMpSmtpStoreId = $this->registry->registry('mp_smtp_store_id');

        if ($isSetMpSmtpStoreId === null) {
            $this->registry->register('mp_smtp_store_id', $storeId);
        }

        $name = [
            $history->getCustomerFirstname(),
            $history->getCustomerLastname(),
        ];
        $message = $this->messageFactory->create();
        $message->addTo($toEmail, implode(' ', $name));
        $message->setSubject($emailSubject);

        if (method_exists($message, 'setFromAddress')) {
            $message->setFromAddress($senderEmail, $senderName);
        } else {
            $message->setFrom($senderEmail, $senderName);
        }

        $emailBody = $this->trackingPixelModifier->execute(
            $history->getPublicKey() ?? '',
            $emailBody,
            (int)$history->getStoreId()
        );
        if (method_exists($message, 'setBodyHtml')) {
            $message->setBodyHtml($emailBody);
        } else {
            $message->setBody($emailBody)
                ->setMessageType(MessageInterface::TYPE_HTML);
        }

        if ($replyToEmail) {
            $message->setReplyTo($replyToEmail);
        }

        if ($bcc) {
            $message->addBcc($bcc);
        }

        if (method_exists($message, 'setPartsToBody')) {
            $message->setPartsToBody();
        }

        // This is a compatibility fill for the implemented EmailMessageInterface in Magento 2.3.3.
        if ($this->messageBuilder) {
            $message = $this->messageBuilder->build($message);
        }

        $mailTransport = $this->mailTransportFactory->create(
            [
                'message' => $message
            ]
        );
        $mailTransport->sendMessage();

        if ($isSetMpSmtpStoreId === null) {
            $this->registry->unregister('mp_smtp_store_id');
        }
    }
}
