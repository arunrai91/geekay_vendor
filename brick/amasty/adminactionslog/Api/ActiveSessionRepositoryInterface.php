<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api;

use Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface;

interface ActiveSessionRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): ActiveSessionInterface;

    /**
     * @param string $sessionId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySessionId(string $sessionId): ActiveSessionInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface $activeSession
     *
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(ActiveSessionInterface $activeSession): ActiveSessionInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface $activeSession
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(ActiveSessionInterface $activeSession): bool;

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
