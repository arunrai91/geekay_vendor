<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\RenderBefore;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterfaceFactory;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Layout implements LoggingActionInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var SessionUserDataProvider
     */
    private $userDataProvider;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var VisitHistoryDetailInterfaceFactory
     */
    private $detailFactory;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $historyEntryRepository;

    /**
     * @var VisitHistoryManager
     */
    private $visitHistoryManager;

    public function __construct(
        DateTime $dateTime,
        MetadataInterface $metadata,
        SessionUserDataProvider $userDataProvider,
        ObjectDataStorageInterface $dataStorage,
        VisitHistoryDetailInterfaceFactory $detailFactory,
        VisitHistoryEntryRepositoryInterface $historyEntryRepository,
        VisitHistoryManager $visitHistoryManager
    ) {
        $this->dateTime = $dateTime;
        $this->metadata = $metadata;
        $this->userDataProvider = $userDataProvider;
        $this->dataStorage = $dataStorage;
        $this->detailFactory = $detailFactory;
        $this->historyEntryRepository = $historyEntryRepository;
        $this->visitHistoryManager = $visitHistoryManager;
    }

    public function execute(): void
    {
        /** @var \Magento\Framework\View\Element\Template\Context $context */
        if (!$context = $this->metadata->getObject()) {
            return;
        }

        $sessionId = $this->userDataProvider->getSessionId();
        $historyEntry = $this->getHistoryEntry($sessionId);
        $details = $historyEntry->getVisitHistoryDetails();
        $lastDetail = end($details) ?: $this->detailFactory->create();

        if ($lastDetail->getPageUrl() !== $context->getUrlBuilder()->getCurrentUrl()) {
            $storageKey = $this->userDataProvider->getUserName() . 'StayDuration';
            $currentTimeStamp = $this->dateTime->gmtTimestamp();
            $lastDetailStayDuration = $this->dataStorage->isExists($storageKey)
                ? $currentTimeStamp - ($this->dataStorage->get($storageKey)['timestamp'] ?? 0)
                : 0;
            $lastDetail->setStayDuration((int)$lastDetailStayDuration);
            $this->dataStorage->set($storageKey, ['timestamp' => $currentTimeStamp]);
            $details[] = $this->detailFactory->create(['data' => [
                VisitHistoryDetail::PAGE_NAME => $context->getPageConfig()->getTitle()->get(),
                VisitHistoryDetail::PAGE_URL => $context->getUrlBuilder()->getCurrentUrl()
            ]]);
            $historyEntry->setVisitHistoryDetails($details);
            $this->historyEntryRepository->save($historyEntry);
        }
    }

    private function getHistoryEntry(string $sessionId): VisitHistoryEntryInterface
    {
        try {
            $historyEntry = $this->historyEntryRepository->getBySessionId($sessionId);
        } catch (NoSuchEntityException $e) {
            $this->visitHistoryManager->startVisit();
            $historyEntry = $this->historyEntryRepository->getBySessionId($sessionId);
        }

        return $historyEntry;
    }
}
