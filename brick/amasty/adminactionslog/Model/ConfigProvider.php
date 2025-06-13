<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'amaudit/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const LOG_ENABLE_VISIT_HISTORY = 'general/log_enable_visit_history';
    public const LOG_ALL_ADMINS = 'general/log_all_admins';
    public const LOG_ADMIN_USERS = 'general/log_admin_users';
    public const ACTIONS_LOG_CLEANING = 'general/auto_cleaning/actions_log_cleaning';
    public const ACTIONS_LOG_PERIOD = 'general/auto_cleaning/actions_log_period';
    public const LOGIN_ATTEMPTS_LOG_CLEANING = 'general/auto_cleaning/login_attempts_log_cleaning';
    public const LOGIN_ATTEMPTS_LOG_PERIOD = 'general/auto_cleaning/login_attempts_log_period';
    public const VISIT_HISTORY_LOG_CLEANING = 'general/auto_cleaning/visit_history_log_cleaning';
    public const VISIT_HISTORY_LOG_PERIOD = 'general/auto_cleaning/visit_history_log_period';
    public const GEOLOCATION_ENABLE = 'general/geolocation_enable';
    public const RESTORE_POPUP_MESSAGE = 'general/restore_popup_message';

    public const SUCCESSFUL_LOG_MAILING_ENABLED = 'email_notifications/successful_log_mailing_enabled';
    public const SUCCESSFUL_LOG_MAILING_IDENTITY = 'email_notifications/successful_log_mailing_identity';
    public const SUCCESSFUL_LOG_MAILING_TEMPLATE = 'email_notifications/successful_log_mailing_template';
    public const SUCCESSFUL_LOG_MAILING_SEND_TO = 'email_notifications/successful_log_mailing_send_to_mail';
    public const UNSUCCESSFUL_LOG_MAILING_ENABLED = 'email_notifications/unsuccessful_log_mailing_enabled';
    public const UNSUCCESSFUL_LOG_MAILING_NUM_ATTEMPTS = 'email_notifications/unsuccessful_log_mailing_number_attempts';
    public const UNSUCCESSFUL_LOG_MAILING_IDENTITY = 'email_notifications/unsuccessful_log_mailing_identity';
    public const UNSUCCESSFUL_LOG_MAILING_TEMPLATE = 'email_notifications/unsuccessful_log_mailing_template';
    public const UNSUCCESSFUL_LOG_MAILING_SEND_TO = 'email_notifications/unsuccessful_log_mailing_send_to_mail';
    public const SUSPICIOUS_LOG_MAILING_ENABLED = 'email_notifications/suspicious_log_mailing_enabled';
    public const SUSPICIOUS_LOG_MAILING_IDENTITY = 'email_notifications/suspicious_log_mailing_identity';
    public const SUSPICIOUS_LOG_MAILING_IF_LOGGED_IN = 'email_notifications/suspicious_log_mailing_if_logged_in';
    public const SUSPICIOUS_LOG_MAILING_TEMPLATE = 'email_notifications/suspicious_log_mailing_template';
    public const SUSPICIOUS_LOG_MAILING_SEND_TO = 'email_notifications/suspicious_log_mailing_send_to_mail';
    /**#@-*/

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledLogVisitHistory($storeId = null): bool
    {
        return $this->isSetFlag(self::LOG_ENABLE_VISIT_HISTORY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledLogAllAdmins($storeId = null): bool
    {
        return $this->isSetFlag(self::LOG_ALL_ADMINS, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getAdminUsers($storeId = null): array
    {
        $adminUsers = [];

        if ($value = $this->getValue(self::LOG_ADMIN_USERS, $storeId)) {
            $adminUsers = explode(',', $value);
        }

        return $adminUsers;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isNeedCleanActionsLog($storeId = null): bool
    {
        return $this->isSetFlag(self::ACTIONS_LOG_CLEANING, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getActionsLogPeriod($storeId = null): int
    {
        return (int)$this->getValue(self::ACTIONS_LOG_PERIOD, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isNeedCleanLoginAttemptsLog($storeId = null): bool
    {
        return $this->isSetFlag(self::LOGIN_ATTEMPTS_LOG_CLEANING, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getLoginAttemptsLogPeriod($storeId = null): int
    {
        return (int)$this->getValue(self::LOGIN_ATTEMPTS_LOG_PERIOD, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isNeedCleanVisitHistoryLog($storeId = null): bool
    {
        return $this->isSetFlag(self::VISIT_HISTORY_LOG_CLEANING, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getVisitHistoryLogPeriod($storeId = null): int
    {
        return (int)$this->getValue(self::VISIT_HISTORY_LOG_PERIOD, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledGeolocation($storeId = null): bool
    {
        return $this->isSetFlag(self::GEOLOCATION_ENABLE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRestoreSettingsText($storeId = null): string
    {
        return (string)$this->getValue(self::RESTORE_POPUP_MESSAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledEmailSuccessfulLoginsToAdmin($storeId = null): bool
    {
        return $this->isSetFlag(self::SUCCESSFUL_LOG_MAILING_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSenderEmailSuccessfulLogins($storeId = null): string
    {
        return (string)$this->getValue(self::SUCCESSFUL_LOG_MAILING_IDENTITY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTemplateEmailSuccessfulLogins($storeId = null): string
    {
        return (string)$this->getValue(self::SUCCESSFUL_LOG_MAILING_TEMPLATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSendToEmailsSuccessfulLogins($storeId = null): array
    {
        $emails = [];

        if ($value = $this->getValue(self::SUCCESSFUL_LOG_MAILING_SEND_TO, $storeId)) {
            $emails = explode(',', $value);
        }

        return $emails;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledEmailUnsuccessfulLoginsToAdmin($storeId = null): bool
    {
        return $this->isSetFlag(self::UNSUCCESSFUL_LOG_MAILING_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getNumberUnsuccessfulLoginAttempts($storeId = null): int
    {
        return (int)$this->getValue(self::UNSUCCESSFUL_LOG_MAILING_NUM_ATTEMPTS, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSenderEmailUnsuccessfulLogins($storeId = null): string
    {
        return (string)$this->getValue(self::UNSUCCESSFUL_LOG_MAILING_IDENTITY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTemplateEmailUnsuccessfulLogins($storeId = null): string
    {
        return (string)$this->getValue(self::UNSUCCESSFUL_LOG_MAILING_TEMPLATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSendToEmailsUnsuccessfulLogins($storeId = null): array
    {
        $emails = [];

        if ($value = $this->getValue(self::UNSUCCESSFUL_LOG_MAILING_SEND_TO, $storeId)) {
            $emails = explode(',', $value);
        }

        return $emails;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledEmailSuspiciousLoginsToAdmin($storeId = null): bool
    {
        return $this->isSetFlag(self::SUSPICIOUS_LOG_MAILING_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getListSuspiciousLoggedInWith($storeId = null): array
    {
        $loggedIn = [];

        if ($value = $this->getValue(self::SUSPICIOUS_LOG_MAILING_IF_LOGGED_IN, $storeId)) {
            $loggedIn = explode(',', $value);
        }

        return $loggedIn;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSenderEmailSuspiciousLogins($storeId = null): string
    {
        return (string)$this->getValue(self::SUSPICIOUS_LOG_MAILING_IDENTITY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTemplateEmailSuspiciousLogins($storeId = null): string
    {
        return (string)$this->getValue(self::SUSPICIOUS_LOG_MAILING_TEMPLATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSendToEmailsSuspiciousLogins($storeId = null): array
    {
        $emails = [];

        if ($value = $this->getValue(self::SUSPICIOUS_LOG_MAILING_SEND_TO, $storeId)) {
            $emails = explode(',', $value);
        }

        return $emails;
    }
}
