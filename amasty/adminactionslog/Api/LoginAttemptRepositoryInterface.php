<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Amasty\AdminActionsLog\Api\Data\LoginAttemptSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface LoginAttemptRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): LoginAttemptInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface $loginAttempt
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(LoginAttemptInterface $loginAttempt): LoginAttemptInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface $loginAttempt
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(LoginAttemptInterface $loginAttempt): bool;

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
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): LoginAttemptSearchResultsInterface;

    /**
     * @param int|null $period
     *
     * @return void
     */
    public function clean(?int $period = null): void;
}
