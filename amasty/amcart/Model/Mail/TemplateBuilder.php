<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\Mail;

use Amasty\Acart\Api\RuleQuoteRepositoryInterface as RuleQuoteRepository;
use Amasty\Acart\Api\RuleRepositoryInterface as RuleRepository;
use Amasty\Acart\Api\ScheduleRepositoryInterface as ScheduleRepository;
use Amasty\Acart\Model\ConfigProvider;
use Amasty\Acart\Model\EmailTemplate\EmailTemplateResolver;
use Amasty\Acart\Model\EmailTemplate\LegacyTemplateProcessor;
use Amasty\Acart\Model\FormatManager;
use Amasty\Acart\Model\History;
use Amasty\Acart\Model\Rule;
use Amasty\Acart\Model\RuleQuote;
use Amasty\Acart\Model\Schedule;
use Amasty\Acart\Model\UrlManager;
use Amasty\Base\Model\MagentoVersion;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class TemplateBuilder
{
    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var LegacyTemplateProcessor
     */
    private $legacyTemplateProcessor;

    /**
     * @var RuleQuoteRepository
     */
    private $ruleQuoteRepository;

    /**
     * @var ScheduleRepository
     */
    private $scheduleRepository;

    /**
     * @var RuleRepository
     */
    private $ruleRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var EmailTemplateResolver
     */
    private $emailTemplateResolver;

    /**
     * @var FormatManager
     */
    private $formatManager;

    /**
     * @var UrlManager
     */
    private $urlManager;

    /**
     * @var EventManagerInterface|null
     */
    private $eventManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        MagentoVersion $magentoVersion,
        LegacyTemplateProcessor $legacyTemplateProcessor,
        RuleQuoteRepository $ruleQuoteRepository,
        ScheduleRepository $scheduleRepository,
        RuleRepository $ruleRepository,
        CartRepositoryInterface $quoteRepository,
        EmailTemplateResolver $emailTemplateResolver,
        FormatManager $formatManager,
        UrlManager $urlManager,
        ?EventManagerInterface $eventManager = null,
        ?ConfigProvider $configProvider = null
    ) {
        $this->magentoVersion = $magentoVersion;
        $this->legacyTemplateProcessor = $legacyTemplateProcessor;
        $this->ruleQuoteRepository = $ruleQuoteRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->ruleRepository = $ruleRepository;
        $this->quoteRepository = $quoteRepository;
        $this->emailTemplateResolver = $emailTemplateResolver;
        $this->formatManager = $formatManager;
        $this->urlManager = $urlManager;
        // OM for backward compatibility
        $this->eventManager = $eventManager ?? ObjectManager::getInstance()->get(EventManagerInterface::class);
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    public function build(History $history, bool $isCustomerDetailsEnabled = false): TemplateInterface
    {
        $ruleQuoteId = $history->getRuleQuoteId();
        $ruleQuote = $this->ruleQuoteRepository->getById($ruleQuoteId);

        $scheduleId = $history->getScheduleId();
        $schedule = $this->scheduleRepository->getById($scheduleId);

        $ruleId = $ruleQuote->getRuleId();
        $rule = $this->ruleRepository->get($ruleId);
        $template = $this->createEmailTemplate($ruleQuote, $schedule, $rule, $history, $isCustomerDetailsEnabled);

        if (version_compare($this->magentoVersion->get(), '2.3.4', '>=')) {
            $this->legacyTemplateProcessor->execute($template);
        }

        return $template;
    }

    private function createEmailTemplate(
        RuleQuote $ruleQuote,
        Schedule $schedule,
        Rule $rule,
        History $history,
        bool $isCustomerDetailsEnabled = false
    ): TemplateInterface {
        $quote = $this->quoteRepository->get($ruleQuote->getQuoteId());
        if ($history->getSalesRuleCoupon()) {
            $quote->setCouponCode($history->getSalesRuleCoupon());
        }
        $quote->collectTotals();
        $discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        $quote->setData('discount', $discount);
        $quote->setData('tax', $this->getTax($quote));

        $template = $this->emailTemplateResolver->execute($schedule);
        $vars = $this->getTemplateVars($ruleQuote, $rule, $quote, $history, $isCustomerDetailsEnabled);
        $template->setVars($vars)
            ->setOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $ruleQuote->getStoreId()
                ]
            );

        return $template;
    }

    private function getTax(Quote $quote): float
    {
        if ($tax = $quote->getTotals()['tax'] ?? null) {
            return (float)$tax->getValue();
        }
        $productTax = .0;

        foreach ($quote->getAllItems() as $item) {
            $productTax += $item->getTaxAmount();
        }

        return $productTax;
    }

    private function getTemplateVars(
        RuleQuote $ruleQuote,
        Rule $rule,
        Quote $quote,
        History $history,
        bool $isCustomerDetailsEnabled = false
    ): array {
        $formatter = $this->formatManager->init(
            [
                FormatManager::TYPE_HISTORY => $history,
                FormatManager::TYPE_QUOTE => $quote,
                FormatManager::TYPE_RULE_QUOTE => $ruleQuote
            ]
        );
        $urlManager = $this->urlManager->init($rule, $history);
        $shippingAmount = $this->getShippingAmount($quote);
        $limit = $this->configProvider->getAbandonedProductLimit();
        $abandonedProductLimit = $limit && $limit < count($quote->getAllVisibleItems()) ? $limit : null;
        // Add Front url instead of Checkout url for email copies.
        $placeOrderUrl = $isCustomerDetailsEnabled
            ? $urlManager->frontUrl()
            : $urlManager->mageUrl('checkout/cart/index');

        $resultArray = [
            'quote' => $quote,
            'quoteId' => $quote->getId(),
            'formattedQuoteTax' => $quote->getTax() ? $formatter->formatPrice('quote', 'tax') : null,
            'formattedShippingAmount' => $shippingAmount
                ? $formatter->formatPriceValue('quote', $shippingAmount)
                : null,
            'quoteDiscount' => $quote->getDiscount(),
            'numberQuoteVisibleItems' => $formatter->countArray($quote->getAllVisibleItems()),
            'abandonedProductLimit' => $abandonedProductLimit,
            'abandonedProductLimitEqualOne' => $abandonedProductLimit === 1,
            'quoteCustomerId' => $quote->getCustomerId(),
            'rule' => $rule,
            'ruleQuote' => $ruleQuote,
            'history' => $history,
            'customerIsGuest' => $quote->getCustomerGroupId() == GroupManagement::NOT_LOGGED_IN_ID,
            'unsubscribeUrl' => $urlManager->unsubscribeUrl(),
            'placeOrderUrl' => $placeOrderUrl,
            'checkoutUrl' => $urlManager->mageUrl('checkout/index/index'),
            'abandonmentDateWithTime' => $formatter->formatTime('quote', 'updated_at'),
            'abandonmentDate' => $formatter->formatDate('quote', 'updated_at'),
            'formattedSubtotal' => $formatter->convertAndFormatPrice('quote', 'base_subtotal'),
            'formattedDiscount' => $formatter->formatPrice('quote', 'discount'),
            'formattedSubtotalWithDiscount' => $formatter->convertAndFormatPrice(
                'quote',
                'base_subtotal_with_discount'
            ),
            'formattedGrandTotal' => $formatter->convertAndFormatPrice('quote', 'base_grand_total'),
            'couponExpirationDate' => $history->getSalesRuleCouponExpirationDate(),
            'isCustomerDetailsEnabled' => $isCustomerDetailsEnabled
        ];
        $vars = new DataObject($resultArray);
        $this->eventManager->dispatch(
            'acart_email_template_vars_prepare',
            ['vars' => $vars, 'history' => $history, 'rule' => $rule]
        );

        return $vars->getData();
    }

    private function getShippingAmount(Quote $quote): ?float
    {
        $totals = $quote->getTotals();
        $shippingAmount = $totals['shipping']['value'] ?? null;

        if (!$shippingAmount) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                $shippingAmount = $shippingAddress->getShippingAmount();
            }
        }

        return $shippingAmount;
    }
}
