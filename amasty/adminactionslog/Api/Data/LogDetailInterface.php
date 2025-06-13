<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

interface LogDetailInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getLogId(): ?int;

    /**
     * @param int $logId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface
     */
    public function setLogId(int $logId): LogDetailInterface;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface
     */
    public function setName(string $name): LogDetailInterface;

    /**
     * @return string|null
     */
    public function getOldValue(): ?string;

    /**
     * @param string $oldValue
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface
     */
    public function setOldValue(string $oldValue): LogDetailInterface;

    /**
     * @return string|null
     */
    public function getNewValue(): ?string;

    /**
     * @param string $newValue
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface
     */
    public function setNewValue(string $newValue): LogDetailInterface;

    /**
     * @return string|null
     */
    public function getModel(): ?string;

    /**
     * @param string $model
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface
     */
    public function setModel(string $model): LogDetailInterface;
}
