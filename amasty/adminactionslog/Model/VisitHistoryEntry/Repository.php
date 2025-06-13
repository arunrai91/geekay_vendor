<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterfaceFactory;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetail as VisitHistoryDetailResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail as VisitHistoryDetailModel;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryEntry as VisitHistoryEntryModel;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Repository implements VisitHistoryEntryRepositoryInterface
{
    /**
     * @var VisitHistoryEntryInterfaceFactory
     */
    private $visitHistoryEntryFactory;

    /**
     * @var ResourceModel\VisitHistoryEntry
     */
    private $visitHistoryEntryResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $visitHistoryEntryCollectionFactory;

    /**
     * @var VisitHistoryDetailResource
     */
    private $visitHistoryDetailResource;

    /**
     * @var DetailLoaderInterface
     */
    private $detailLoader;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $visitHistoryEntries = [];

    /**
     * @var array
     */
    private $visitHistoryEntriesBySessionId = [];

    public function __construct(
        VisitHistoryEntryInterfaceFactory $visitHistoryEntryFactory,
        ResourceModel\VisitHistoryEntry $visitHistoryEntryResource,
        ResourceModel\CollectionFactory $visitHistoryEntryCollectionFactory,
        VisitHistoryDetailResource $visitHistoryDetailResource,
        DetailLoaderInterface $detailLoader,
        DateTime $dateTime
    ) {
        $this->visitHistoryEntryFactory = $visitHistoryEntryFactory;
        $this->visitHistoryEntryResource = $visitHistoryEntryResource;
        $this->visitHistoryEntryCollectionFactory = $visitHistoryEntryCollectionFactory;
        $this->visitHistoryDetailResource = $visitHistoryDetailResource;
        $this->detailLoader = $detailLoader;
        $this->dateTime = $dateTime;
    }

    public function getById(int $id): VisitHistoryEntryInterface
    {
        if (!isset($this->visitHistoryEntries[$id])) {
            /** @var VisitHistoryEntryInterface $visitHistoryEntry */
            $visitHistoryEntry = $this->visitHistoryEntryFactory->create();
            $this->visitHistoryEntryResource->load($visitHistoryEntry, $id);
            if (!$visitHistoryEntry->getId()) {
                throw new NoSuchEntityException(__('Visit History Entry with specified ID "%1" not found.', $id));
            }
            $visitHistoryEntry->setVisitHistoryDetails($this->detailLoader->loadDetails($id));

            $this->visitHistoryEntries[$id] = $visitHistoryEntry;
        }

        return $this->visitHistoryEntries[$id];
    }

    public function getBySessionId(string $sessionId): VisitHistoryEntryInterface
    {
        if (!isset($this->visitHistoryEntriesBySessionId[$sessionId])) {
            $historyEntryId = $this->resolveHistoryIdBySessionId($sessionId);
            $this->visitHistoryEntriesBySessionId[$sessionId] = $this->getById($historyEntryId);
        }

        return $this->visitHistoryEntriesBySessionId[$sessionId];
    }

    public function save(VisitHistoryEntryInterface $visitHistoryEntry): VisitHistoryEntryInterface
    {
        try {
            if ($visitHistoryEntry->getId()) {
                $visitHistoryEntry = $this->getById((int)$visitHistoryEntry->getId())
                    ->addData($visitHistoryEntry->getData());
            }
            $this->visitHistoryEntryResource->save($visitHistoryEntry);

            /** @var VisitHistoryDetailModel $visitHistoryDetail */
            foreach ($visitHistoryEntry->getVisitHistoryDetails() as $visitHistoryDetail) {
                $visitHistoryDetail->setVisitId((int)$visitHistoryEntry->getId());
                $visitHistoryDetail->setSessionId((string)$visitHistoryEntry->getSessionId());
                $this->visitHistoryDetailResource->save($visitHistoryDetail);
            }

            unset(
                $this->visitHistoryEntries[$visitHistoryEntry->getId()],
                $this->visitHistoryEntriesBySessionId[$visitHistoryEntry->getSessionId()]
            );
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Visit History. Error: %1', $e->getMessage()));
        }

        return $visitHistoryEntry;
    }

    public function delete(VisitHistoryEntryInterface $visitHistoryEntry): bool
    {
        try {
            $this->visitHistoryEntryResource->delete($visitHistoryEntry);
            unset(
                $this->visitHistoryEntries[$visitHistoryEntry->getId()],
                $this->visitHistoryEntriesBySessionId[$visitHistoryEntry->getSessionId()]
            );
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to delete the Visit History. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }

    public function clean(?int $period = null): void
    {
        $connection = $this->visitHistoryEntryResource->getConnection();
        $tableName = $this->visitHistoryEntryResource->getMainTable();

        if ($period === null) {
            $connection->delete($tableName);
        } else {
            $field = new \Zend_Db_Expr(
                sprintf('IFNULL(%s, %s)', VisitHistoryEntryModel::SESSION_END, VisitHistoryEntryModel::SESSION_START)
            );
            $time = '-' . $period . ' days';
            $connection->delete(
                $tableName,
                [$field . ' < ?' => $this->dateTime->gmtDate('Y-m-d H:i:s', $time)]
            );
        }
    }

    private function resolveHistoryIdBySessionId(string $sessionId): int
    {
        $historyEntryCollection = $this->visitHistoryEntryCollectionFactory->create();
        $historyEntryCollection->addFieldToFilter(VisitHistoryEntryModel::SESSION_ID, $sessionId);
        $historyEntryCollection->setPageSize(1);
        /** @var VisitHistoryDetailInterface $historyEntry */
        $historyEntry = $historyEntryCollection->getFirstItem();

        return (int)$historyEntry->getId();
    }
}
