<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntrySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface LogEntryRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): LogEntryInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\LogEntryInterface $logEntry
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(LogEntryInterface $logEntry): LogEntryInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\LogEntryInterface $logEntry
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(LogEntryInterface $logEntry): bool;

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntrySearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): LogEntrySearchResultsInterface;

    /**
     * @param int|null $period
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function clean(?int $period = null): void;

    /**
     * @param array|null $storeIds
     *
     * @return void
     */
    public function cleanByStoreIds(?array $storeIds = []): void;
}
