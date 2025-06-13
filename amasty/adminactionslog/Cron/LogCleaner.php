<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Cron;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\LoginAttemptRepositoryInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\ConfigProvider;

class LogCleaner
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var LoginAttemptRepositoryInterface
     */
    private $loginAttemptRepository;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $visitHistoryEntryRepository;

    public function __construct(
        ConfigProvider $configProvider,
        LogEntryRepositoryInterface $logEntryRepository,
        LoginAttemptRepositoryInterface $loginAttemptRepository,
        VisitHistoryEntryRepositoryInterface $visitHistoryEntryRepository
    ) {
        $this->configProvider = $configProvider;
        $this->logEntryRepository = $logEntryRepository;
        $this->loginAttemptRepository = $loginAttemptRepository;
        $this->visitHistoryEntryRepository = $visitHistoryEntryRepository;
    }

    public function execute()
    {
        if ($this->configProvider->isNeedCleanActionsLog()) {
            $this->logEntryRepository->clean($this->configProvider->getActionsLogPeriod());
        }

        if ($this->configProvider->isNeedCleanLoginAttemptsLog()) {
            //TODO: replace to LoginAttemptsManager->clear()
            $this->loginAttemptRepository->clean($this->configProvider->getLoginAttemptsLogPeriod());
        }

        if ($this->configProvider->isNeedCleanVisitHistoryLog()) {
            //TODO: replace to VisitHistoryManager->clear()
            $this->visitHistoryEntryRepository->clean($this->configProvider->getVisitHistoryLogPeriod());
        }
    }
}
