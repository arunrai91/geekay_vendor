<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const XPATH_ABANDONED_GAP = 'general/abandoned_gap';
    public const XPATH_SEND_ONETIME = 'general/send_onetime';
    public const XPATH_DISABLE_LOGGING_FOR_GUESTS = 'general/disable_logging_for_guests';
    public const XPATH_AUTO_LOGIN_ENABLE = 'general/auto_login_enable';
    public const XPATH_AUTO_LOGIN_LIFETIME = 'general/auto_login_lifetime';
    public const XPATH_EEA_COUNTRIES = 'general/eea_countries';
    public const XPATH_HISTORY_CLEAN_DAYS = 'general/history_clean_days';
    public const XPATH_DEBUG_MODE_EMAIL_DOMAINS = 'debug/debug_emails';
    public const XPATH_DEBUG_MODE_ENABLE = 'debug/debug_enable';
    public const XPATH_TEST_RECIPIENT = 'testing/recipient_email';
    public const XPATH_SAFE_MODE = 'testing/safe_mode';
    public const XPATH_EMAIL_TEMPLATES_BCC = 'email_templates/bcc';
    public const XPATH_EMAIL_SENDER_NAME = 'email_templates/sender_name';
    public const XPATH_REPLY_EMAIL = 'email_templates/reply_email';
    public const XPATH_EMAIL_SENDER_IDENTITY = 'email_templates/sender_email_identity';
    public const XPATH_EMAIL_TEMPLATES_COPY_METHOD = 'email_templates/copy_method';
    public const XPATH_CUSTOMER_CONTACT_DETAILS = 'email_templates/customer_contact_details';
    public const XPATH_ONLY_CUSTOMERS = 'email_templates/only_customers';
    public const XPATH_IMG_URL_WITHOUT_PUB = 'email_templates/img_url_without_pub';
    public const XPATH_SEND_TO_SUBSCRIBERS_ONLY = 'email_templates/emails_to_newsletter_subscribers_only';
    public const XPATH_ABANDONED_PRODUCT_LIMIT = 'email_templates/abandoned_product_limit';
    public const XPATH_PRODUCTS_QTY = 'email_templates/products_qty';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_acart/';

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EmailValidator $emailValidator
    ) {
        parent::__construct($scopeConfig);
        $this->emailValidator = $emailValidator;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getGlobalValue($key)
    {
        return $this->getValue($key, null, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    public function isAutoLoginEnabled(): bool
    {
        return (bool)$this->getGlobalValue(self::XPATH_AUTO_LOGIN_ENABLE);
    }

    public function getAutoLoginLifetime(): int
    {
        return (int)$this->getValue(self::XPATH_AUTO_LOGIN_LIFETIME);
    }

    public function isDebugMode(): bool
    {
        return (bool)$this->getGlobalValue(self::XPATH_DEBUG_MODE_ENABLE);
    }

    public function isSendOnetime(): bool
    {
        return (bool)$this->getGlobalValue(self::XPATH_SEND_ONETIME);
    }

    public function isCustomerContactDetails(): bool
    {
        return (bool)$this->getValue(self::XPATH_CUSTOMER_CONTACT_DETAILS);
    }

    public function isOnlyCustomers(): bool
    {
        return (bool)$this->getGlobalValue(self::XPATH_ONLY_CUSTOMERS);
    }

    public function getAbandonedGap(): int
    {
        return (int)$this->getValue(self::XPATH_ABANDONED_GAP);
    }

    public function getRecipientEmailForTest(): string
    {
        $recipientEmail = $this->getGlobalValue(self::XPATH_TEST_RECIPIENT);
        if (!$this->emailValidator->isValid($recipientEmail)) {
            $recipientEmail = '';
        }

        return $recipientEmail;
    }

    public function getDebugEnabledEmailDomains(): array
    {
        $result = [];
        if ($this->isDebugMode()) {
            $result = explode(',', (string)$this->getGlobalValue(self::XPATH_DEBUG_MODE_EMAIL_DOMAINS));
        }

        return array_filter($result);
    }

    public function getRemovePubFromImgUrl(): bool
    {
        return (bool)$this->getGlobalValue(self::XPATH_IMG_URL_WITHOUT_PUB);
    }

    public function getBcc($storeId): string
    {
        return (string)$this->getValue(self::XPATH_EMAIL_TEMPLATES_BCC, $storeId);
    }

    public function getCopyMethod($storeId): string
    {
        return (string)$this->getValue(self::XPATH_EMAIL_TEMPLATES_COPY_METHOD, $storeId);
    }

    public function getSenderName($storeId = null): string
    {
        return (string)$this->getValue(self::XPATH_EMAIL_SENDER_NAME, $storeId);
    }

    public function getSenderEmail($storeId = null): string
    {
        $emailSenderIdentity = (string)$this->getValue(self::XPATH_EMAIL_SENDER_IDENTITY, $storeId);

        return (string)$this->scopeConfig->getValue(
            'trans_email/ident_' . $emailSenderIdentity . '/email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getReplyToEmail($storeId = null): string
    {
        return trim($this->getValue(self::XPATH_REPLY_EMAIL, $storeId) ?? '');
    }

    public function isSafeMode($storeId = null): bool
    {
        return (bool)$this->getValue(self::XPATH_SAFE_MODE, $storeId);
    }

    public function isDisableLoggingForGuests($storeId = null): bool
    {
        return (bool)$this->getValue(self::XPATH_DISABLE_LOGGING_FOR_GUESTS, $storeId);
    }

    public function getProductsQty(): int
    {
        return (int)$this->getValue(self::XPATH_PRODUCTS_QTY);
    }

    public function getHistoryAutoCleanDays(): int
    {
        return (int)$this->getValue(self::XPATH_HISTORY_CLEAN_DAYS);
    }

    public function isEmailsToNewsletterSubscribersOnly($storeId = null): bool
    {
        return (bool)$this->getValue(self::XPATH_SEND_TO_SUBSCRIBERS_ONLY, $storeId);
    }

    public function getEEACountries(): array
    {
        return explode(',', (string)$this->getValue(self::XPATH_EEA_COUNTRIES));
    }

    public function getAbandonedProductLimit(): ?int
    {
        $limit = $this->getValue(self::XPATH_ABANDONED_PRODUCT_LIMIT);

        return $limit ? (int)$limit : null;
    }
}
