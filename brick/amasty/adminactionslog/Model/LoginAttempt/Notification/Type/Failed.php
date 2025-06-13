<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\Notification\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Amasty\AdminActionsLog\Model\LoginAttempt\LoginAttempt;
use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\CollectionFactory as AttemptCollectionFactory;
use Amasty\AdminActionsLog\Model\OptionSource\LoginAttemptStatus;
use Amasty\AdminActionsLog\Utils\EmailSender;
use Magento\Framework\App\Area;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Failed implements AttemptNotificatorInterface
{
    public const STORAGE_KEY = 'failed_notified_at';

    /**
     * @var AttemptCollectionFactory
     */
    private $attemptCollectionFactory;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var int
     */
    private $failedRangeDuration;

    /**
     * @var int
     */
    private $minFailedCount;

    public function __construct(
        AttemptCollectionFactory $attemptCollectionFactory,
        ObjectDataStorageInterface $dataStorage,
        ConfigProvider $configProvider,
        EmailSender $emailSender,
        DateTime $dateTime,
        int $failedRangeDuration = 3600,
        int $minFailedCount = 5
    ) {
        $this->attemptCollectionFactory = $attemptCollectionFactory;
        $this->dataStorage = $dataStorage;
        $this->configProvider = $configProvider;
        $this->emailSender = $emailSender;
        $this->dateTime = $dateTime;
        $this->failedRangeDuration = $failedRangeDuration;
        $this->minFailedCount = $minFailedCount;
    }

    public function execute(LoginAttemptInterface $loginAttempt): void
    {
        $isEnabled = $this->configProvider->isEnabledEmailUnsuccessfulLoginsToAdmin();
        $lastNotificationTimestamp = $this->dataStorage->get(self::STORAGE_KEY)['timestamp'] ?? 1;
        $failedAttemptsCount = $this->getFailedAttemptsCount($lastNotificationTimestamp);
        $minFailedAttemptsCount = $this->configProvider->getNumberUnsuccessfulLoginAttempts() ?: $this->minFailedCount;

        if ($isEnabled && $failedAttemptsCount >= $minFailedAttemptsCount) {
            $this->emailSender->sendEmail(
                $this->configProvider->getSendToEmailsUnsuccessfulLogins(),
                $this->configProvider->getTemplateEmailUnsuccessfulLogins(),
                $this->configProvider->getSenderEmailUnsuccessfulLogins(),
                ['unsuccessful_login_count' => $failedAttemptsCount],
                Area::AREA_ADMINHTML
            );
            $this->dataStorage->set(self::STORAGE_KEY, ['timestamp' => $this->dateTime->gmtTimestamp()]);
        }
    }

    private function getFailedAttemptsCount(int $lastNotificationTimestamp): int
    {
        $currentTimestamp = $this->dateTime->gmtTimestamp();
        if ($this->failedRangeDuration > $currentTimestamp - $lastNotificationTimestamp) {
            return 0;
        }

        $attemptCollection = $this->attemptCollectionFactory->create();
        $attemptCollection->addFieldToFilter(LoginAttempt::STATUS, LoginAttemptStatus::FAILED);
        $attemptCollection->addFieldToFilter(
            LoginAttempt::DATE,
            ['gteq' => $this->dateTime->gmtDate(null, $lastNotificationTimestamp)]
        );

        return $attemptCollection->count();
    }
}
