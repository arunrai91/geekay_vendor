<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterfaceFactory;
use Amasty\AdminActionsLog\Api\Data\LogEntrySearchResultsInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntrySearchResultsInterfaceFactory;
use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogDetail as LogDetailModel;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail as LogDetailResource;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetailCollectionFactory;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Repository implements LogEntryRepositoryInterface
{
    /**
     * @var LogEntrySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LogEntryInterfaceFactory
     */
    private $logEntryFactory;

    /**
     * @var ResourceModel\LogEntry
     */
    private $logEntryResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $logEntryCollectionFactory;

    /**
     * @var LogDetailResource
     */
    private $logDetailResource;

    /**
     * @var LogDetailCollectionFactory
     */
    private $logDetailCollectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $logEntries = [];

    public function __construct(
        LogEntrySearchResultsInterfaceFactory $searchResultsFactory,
        LogEntryInterfaceFactory $logEntryFactory,
        ResourceModel\LogEntry $logEntryResource,
        ResourceModel\CollectionFactory $logEntryCollectionFactory,
        LogDetailResource $logDetailResource,
        LogDetailCollectionFactory $logDetailCollectionFactory,
        DateTime $dateTime,
        Serializer $serializer
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logEntryFactory = $logEntryFactory;
        $this->logEntryResource = $logEntryResource;
        $this->logEntryCollectionFactory = $logEntryCollectionFactory;
        $this->logDetailResource = $logDetailResource;
        $this->logDetailCollectionFactory = $logDetailCollectionFactory;
        $this->dateTime = $dateTime;
        $this->serializer = $serializer;
    }

    public function getById(int $id): LogEntryInterface
    {
        if (!isset($this->logEntries[$id])) {
            /** @var LogEntryInterface $logEntry */
            $logEntry = $this->logEntryFactory->create();
            $this->logEntryResource->load($logEntry, $id);
            if (!$logEntry->getId()) {
                throw new NoSuchEntityException(__('Log Entry with specified ID "%1" not found.', $id));
            }
            $logEntry->setLogDetails($this->getLogDetails((int)$logEntry->getId()));
            $logEntry->setAdditionalData($this->serializer->unserialize($logEntry->getAdditionalData()));

            $this->logEntries[$id] = $logEntry;
        }

        return $this->logEntries[$id];
    }

    public function save(LogEntryInterface $logEntry): LogEntryInterface
    {
        try {
            if ($logEntry->getId()) {
                $logEntry = $this->getById((int)$logEntry->getId())->addData($logEntry->getData());
            }

            if ($logEntry->getAdditionalData()) {
                $logEntry->setAdditionalData($this->serializer->serialize($logEntry->getAdditionalData()));
            }

            $this->logEntryResource->save($logEntry);

            /** @var LogDetailModel $logDetail */
            foreach ($logEntry->getLogDetails() as $logDetail) {
                $logDetail->setLogId((int)$logEntry->getId());
                $this->logDetailResource->save($logDetail);
            }

            unset($this->logEntries[$logEntry->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Log Entry. Error: %1', $e->getMessage()));
        }

        return $logEntry;
    }

    public function delete(LogEntryInterface $logEntry): bool
    {
        try {
            $this->logEntryResource->delete($logEntry);
            unset($this->logEntries[$logEntry->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to delete the Log Entry. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }

    public function getList(SearchCriteriaInterface $searchCriteria): LogEntrySearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var ResourceModel\Collection $logEntryCollection */
        $logEntryCollection = $this->logEntryCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $logEntryCollection);
        }
        $searchResults->setTotalCount($logEntryCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $logEntryCollection);
        }
        $logEntryCollection->setCurPage($searchCriteria->getCurrentPage());
        $logEntryCollection->setPageSize($searchCriteria->getPageSize());
        $logEntries = [];
        /** @var LogEntryInterface $logEntry */
        foreach ($logEntryCollection->getItems() as $logEntry) {
            $logEntries[] = $this->getById((int)$logEntry->getId());
        }
        $searchResults->setItems($logEntries);

        return $searchResults;
    }

    public function clean(?int $period = null): void
    {
        $connection = $this->logEntryResource->getConnection();
        $tableName = $this->logEntryResource->getMainTable();

        if ($period === null) {
            $connection->delete($tableName);
        } else {
            $time = '-' . $period . ' days';
            $connection->delete(
                $tableName,
                [LogEntry::DATE . ' < ?' => $this->dateTime->gmtDate('Y-m-d H:i:s', $time)]
            );
        }
    }

    public function cleanByStoreIds(?array $storeIds = []): void
    {
        $connection = $this->logEntryResource->getConnection();
        $tableName = $this->logEntryResource->getMainTable();

        $connection->delete(
            $tableName,
            [LogEntry::STORE_ID . ' IN (?)' => $storeIds]
        );
    }

    private function getLogDetails(int $logEntryId): array
    {
        /** @var ResourceModel\LogDetailCollection $collection */
        $collection = $this->logDetailCollectionFactory->create();
        $collection->addFieldToFilter(LogDetailModel::LOG_ID, $logEntryId);

        return $collection->getItems();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param ResourceModel\Collection  $logEntryCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        ResourceModel\Collection $logEntryCollection
    ): void {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $logEntryCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param ResourceModel\Collection  $logEntryCollection
     *
     * @return void
     */
    private function addOrderToCollection(
        array $sortOrders,
        ResourceModel\Collection $logEntryCollection
    ): void {
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $logEntryCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
