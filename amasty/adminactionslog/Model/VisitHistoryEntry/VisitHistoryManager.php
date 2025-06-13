<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterfaceFactory;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryManagerInterface;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;

class VisitHistoryManager implements VisitHistoryManagerInterface
{
    /**
     * @var SessionUserDataProvider
     */
    private $sessionUserDataProvider;

    /**
     * @var VisitHistoryEntryInterfaceFactory
     */
    private $visitHistoryEntryFactory;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $visitHistoryEntryRepository;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        SessionUserDataProvider $sessionUserDataProvider,
        VisitHistoryEntryInterfaceFactory $visitHistoryEntryFactory,
        VisitHistoryEntryRepositoryInterface $visitHistoryEntryRepository,
        DateTime $dateTime
    ) {
        $this->sessionUserDataProvider = $sessionUserDataProvider;
        $this->visitHistoryEntryFactory = $visitHistoryEntryFactory;
        $this->visitHistoryEntryRepository = $visitHistoryEntryRepository;
        $this->dateTime = $dateTime;
    }

    public function startVisit(): void
    {
        $userData = $this->sessionUserDataProvider->getUserPreparedData();
        $visitHistoryEntryModel = $this->visitHistoryEntryFactory->create(['data' => $userData]);
        $visitHistoryEntryModel->setSessionStart($this->dateTime->date());

        $this->visitHistoryEntryRepository->save($visitHistoryEntryModel);
    }

    public function endVisit(string $sessionId = null): void
    {
        $sessionId = $sessionId ?: $this->sessionUserDataProvider->getSessionId();
        $visitHistoryEntryModel = $this->visitHistoryEntryRepository->getBySessionId($sessionId);
        $visitHistoryEntryModel->setSessionEnd($this->dateTime->date());
        $this->visitHistoryEntryRepository->save($visitHistoryEntryModel);
    }

    public function clear(int $period = null): void
    {
        $this->visitHistoryEntryRepository->clean($period);
    }
}
