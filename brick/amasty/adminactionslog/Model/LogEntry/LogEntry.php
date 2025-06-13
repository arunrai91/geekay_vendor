<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Api\Data\LogEntryExtensionInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class LogEntry extends AbstractExtensibleModel implements LogEntryInterface
{
    public const ID = 'id';
    public const DATE = 'date';
    public const USERNAME = 'username';
    public const TYPE = 'type';
    public const CATEGORY = 'category';
    public const CATEGORY_NAME = 'category_name';
    public const PARAMETER_NAME = 'parameter_name';
    public const ELEMENT_ID = 'element_id';
    public const VIEW_ELEMENT_ID = 'view_element_id';
    public const ITEM = 'item';
    public const IP = 'ip';
    public const STORE_ID = 'store_id';
    public const ADDITIONAL_DATA = 'additional_data';
    public const LOG_DETAILS = 'log_details';
    public const INTERACTION_AREA = 'interaction_area';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amaudit_log_entry';

    /**
     * @var string
     */
    protected $_eventObject = 'log_entry';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\LogEntry::class);
        $this->setIdFieldName(self::ID);
    }

    public function getDate(): ?string
    {
        return $this->_getData(self::DATE);
    }

    public function setDate(string $date): LogEntryInterface
    {
        $this->setData(self::DATE, $date);

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->_getData(self::USERNAME);
    }

    public function setUsername(string $username): LogEntryInterface
    {
        $this->setData(self::USERNAME, $username);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->_getData(self::TYPE);
    }

    public function setType(string $type): LogEntryInterface
    {
        $this->setData(self::TYPE, $type);

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->_getData(self::CATEGORY);
    }

    public function setCategory(string $category): LogEntryInterface
    {
        $this->setData(self::CATEGORY, $category);

        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->_getData(self::CATEGORY_NAME);
    }

    public function setCategoryName(string $categoryName): LogEntryInterface
    {
        $this->setData(self::CATEGORY_NAME, $categoryName);

        return $this;
    }

    public function getParameterName(): ?string
    {
        return $this->_getData(self::PARAMETER_NAME);
    }

    public function setParameterName(string $parameterName): LogEntryInterface
    {
        $this->setData(self::PARAMETER_NAME, $parameterName);

        return $this;
    }

    public function getElementId(): int
    {
        return (int)$this->_getData(self::ELEMENT_ID);
    }

    public function setElementId(int $elementId): LogEntryInterface
    {
        $this->setData(self::ELEMENT_ID, $elementId);

        return $this;
    }

    public function getViewElementId(): ?int
    {
        return $this->hasData(self::VIEW_ELEMENT_ID)
            ? (int)$this->_getData(self::VIEW_ELEMENT_ID)
            : null;
    }

    public function setViewElementId(?int $viewElementId): LogEntryInterface
    {
        $this->setData(self::VIEW_ELEMENT_ID, $viewElementId);

        return $this;
    }

    public function getItem(): ?string
    {
        return $this->_getData(self::ITEM);
    }

    public function setItem(string $item): LogEntryInterface
    {
        $this->setData(self::ITEM, $item);

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->_getData(self::IP);
    }

    public function setIp(string $ipAddress): LogEntryInterface
    {
        $this->setData(self::IP, $ipAddress);

        return $this;
    }

    public function getStoreId(): ?int
    {
        return $this->hasData(self::STORE_ID) ? (int)$this->_getData(self::STORE_ID) : null;
    }

    public function setStoreId(int $storeId): LogEntryInterface
    {
        $this->setData(self::STORE_ID, $storeId);

        return $this;
    }

    public function getAdditionalData()
    {
        return $this->_getData(self::ADDITIONAL_DATA);
    }

    public function setAdditionalData($additionalData): LogEntryInterface
    {
        $this->setData(self::ADDITIONAL_DATA, $additionalData);

        return $this;
    }

    public function getLogDetails(): array
    {
        return (array)$this->_getData(self::LOG_DETAILS);
    }

    public function setLogDetails(array $logDetails): LogEntryInterface
    {
        $this->setData(self::LOG_DETAILS, $logDetails);

        return $this;
    }

    public function getInteractionArea(): ?string
    {
        return $this->hasData(self::INTERACTION_AREA)
            ? (string)$this->_getData(self::INTERACTION_AREA)
            : null;
    }

    public function setInteractionArea(string $area): LogEntryInterface
    {
        return $this->setData(self::INTERACTION_AREA, $area);
    }

    public function getExtensionAttributes(): LogEntryExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        ?LogEntryExtensionInterface $extensionAttributes = null
    ): LogEntryInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
