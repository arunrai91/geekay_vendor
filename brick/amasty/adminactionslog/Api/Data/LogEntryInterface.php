<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

use Amasty\AdminActionsLog\Api\Data\LogEntryExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface LogEntryInterface extends ExtensibleDataInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param string $date
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setDate(string $date): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * @param string $username
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setUsername(string $username): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string $type
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setType(string $type): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getCategory(): ?string;

    /**
     * @param string $category
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setCategory(string $category): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getCategoryName(): ?string;

    /**
     * @param string $categoryName
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setCategoryName(string $categoryName): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getParameterName(): ?string;

    /**
     * @param string $parameterName
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setParameterName(string $parameterName): LogEntryInterface;

    /**
     * @return int
     */
    public function getElementId(): int;

    /**
     * @param int $elementId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setElementId(int $elementId): LogEntryInterface;

    /**
     * @return int|null
     */
    public function getViewElementId(): ?int;

    /**
     * @param int|null $viewElementId
     *
     * @return LogEntryInterface
     */
    public function setViewElementId(?int $viewElementId): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getItem(): ?string;

    /**
     * @param string $item
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setItem(string $item): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getIp(): ?string;

    /**
     * @param string $ipAddress
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setIp(string $ipAddress): LogEntryInterface;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int $storeId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setStoreId(int $storeId): LogEntryInterface;

    /**
     * @return mixed
     */
    public function getAdditionalData();

    /**
     * @param mixed $additionalData
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setAdditionalData($additionalData): LogEntryInterface;

    /**
     * @return \Amasty\AdminActionsLog\Api\Data\LogDetailInterface[]
     */
    public function getLogDetails(): array;

    /**
     * @param array $logDetails
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setLogDetails(array $logDetails): LogEntryInterface;

    /**
     * @return string|null
     */
    public function getInteractionArea(): ?string;

    /**
     * @param string $area
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setInteractionArea(string $area): LogEntryInterface;

    /**
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryExtensionInterface
     */
    public function getExtensionAttributes(): LogEntryExtensionInterface;

    /**
     * @param \Amasty\AdminActionsLog\Api\Data\LogEntryExtensionInterface|null $extensionAttributes
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LogEntryInterface
     */
    public function setExtensionAttributes(?LogEntryExtensionInterface $extensionAttributes = null): LogEntryInterface;
}
