<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\Notification\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection\Detector;
use Amasty\AdminActionsLog\Utils\EmailSender;
use Magento\Framework\App\Area;

class Succeed implements AttemptNotificatorInterface
{
    /**
     * @var Detector
     */
    private $suspiciousDetector;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Detector $suspiciousDetector,
        EmailSender $emailSender,
        ConfigProvider $configProvider
    ) {
        $this->suspiciousDetector = $suspiciousDetector;
        $this->emailSender = $emailSender;
        $this->configProvider = $configProvider;
    }

    public function execute(LoginAttemptInterface $loginAttempt): void
    {
        if ($this->isSuspiciousLogin($loginAttempt)) {
            $isEnabled = $this->configProvider->isEnabledEmailSuspiciousLoginsToAdmin();
            $recipients = $this->configProvider->getSendToEmailsSuspiciousLogins();
            $template = $this->configProvider->getTemplateEmailSuspiciousLogins();
            $sender = $this->configProvider->getSenderEmailSuspiciousLogins();
        } else {
            $isEnabled = $this->configProvider->isEnabledEmailSuccessfulLoginsToAdmin();
            $recipients = $this->configProvider->getSendToEmailsSuccessfulLogins();
            $template = $this->configProvider->getTemplateEmailSuccessfulLogins();
            $sender = $this->configProvider->getSenderEmailSuccessfulLogins();
        }

        if ($isEnabled) {
            $this->emailSender->sendEmail(
                $recipients,
                $template,
                $sender,
                [
                    'username' => $loginAttempt->getUsername(),
                    'full_name' => $loginAttempt->getFullName(),
                    'ip' => $loginAttempt->getIp(),
                    'country_id' => $loginAttempt->getCountryId()
                ],
                Area::AREA_ADMINHTML
            );
        }
    }

    private function isSuspiciousLogin(LoginAttemptInterface $loginAttempt): bool
    {
        foreach ($this->configProvider->getListSuspiciousLoggedInWith() as $detectionType) {
            if ($this->suspiciousDetector->isSuspicious($detectionType, $loginAttempt)) {
                return true;
            }
        }

        return false;
    }
}
