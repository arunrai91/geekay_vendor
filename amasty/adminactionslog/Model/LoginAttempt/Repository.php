<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptSearchResultsInterface;
use Amasty\AdminActionsLog\Api\Data\LoginAttemptSearchResultsInterfaceFactory;
use Amasty\AdminActionsLog\Api\LoginAttemptRepositoryInterface;
use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterfaceFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Repository implements LoginAttemptRepositoryInterface
{
    /**
     * @var LoginAttemptSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LoginAttemptInterfaceFactory
     */
    private $loginAttemptFactory;

    /**
     * @var ResourceModel\LoginAttempt
     */
    private $resource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $loginAttempts = [];

    public function __construct(
        LoginAttemptSearchResultsInterfaceFactory $searchResultsFactory,
        LoginAttemptInterfaceFactory $loginAttemptFactory,
        ResourceModel\LoginAttempt $resource,
        ResourceModel\CollectionFactory $collectionFactory,
        DateTime $dateTime
    ) {
        $this->loginAttemptFactory = $loginAttemptFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dateTime = $dateTime;
    }

    public function getById(int $id): LoginAttemptInterface
    {
        if (!isset($this->loginAttempts[$id])) {
            /** @var LoginAttemptInterface $loginAttempt */
            $loginAttempt = $this->loginAttemptFactory->create();
            $this->resource->load($loginAttempt, $id);
            if (!$loginAttempt->getId()) {
                throw new NoSuchEntityException(__('Login Attempt with specified ID "%1" not found.', $id));
            }

            $this->loginAttempts[$id] = $loginAttempt;
        }

        return $this->loginAttempts[$id];
    }

    public function save(LoginAttemptInterface $loginAttempt): LoginAttemptInterface
    {
        try {
            if ($loginAttempt->getId()) {
                $loginAttempt = $this->getById((int)$loginAttempt->getId())->addData($loginAttempt->getData());
            }
            $this->resource->save($loginAttempt);

            unset($this->loginAttempts[$loginAttempt->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Login Attempt. Error: %1', $e->getMessage()));
        }

        return $loginAttempt;
    }

    public function delete(LoginAttemptInterface $loginAttempt): bool
    {
        try {
            $this->resource->delete($loginAttempt);
            unset($this->loginAttempts[$loginAttempt->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to delete the Login Attempt. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }

    public function getList(SearchCriteriaInterface $searchCriteria): LoginAttemptSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var ResourceModel\Collection $loginAttemptCollection */
        $loginAttemptCollection = $this->collectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $loginAttemptCollection);
        }
        $searchResults->setTotalCount($loginAttemptCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $loginAttemptCollection);
        }
        $loginAttemptCollection->setCurPage($searchCriteria->getCurrentPage());
        $loginAttemptCollection->setPageSize($searchCriteria->getPageSize());
        $loginAttempts = [];
        /** @var LoginAttemptInterface $loginAttempt */
        foreach ($loginAttemptCollection->getItems() as $loginAttempt) {
            $loginAttempts[] = $this->getById((int)$loginAttempt->getId());
        }
        $searchResults->setItems($loginAttempts);

        return $searchResults;
    }

    public function clean(?int $period = null): void
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getMainTable();

        if ($period === null) {
            $connection->truncateTable($tableName);
        } else {
            $time = '-' . $period . ' days';
            $connection->delete(
                $tableName,
                [LoginAttempt::DATE . ' < ?' => $this->dateTime->gmtDate('Y-m-d H:i:s', $time)]
            );
        }
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
