<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceHash;

use Amasty\Base\Api\Data\InstanceHashInterface;
use Magento\Framework\Model\AbstractModel;

class InstanceHash extends AbstractModel implements InstanceHashInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\InstanceHash::class);
        $this->setIdFieldName(InstanceHashInterface::ID);
    }

    public function getId(): ?int
    {
        return $this->hasData(InstanceHashInterface::ID)
            ? (int)$this->_getData(InstanceHashInterface::ID)
            : null;
    }

    public function setCode(string $code): void
    {
        $this->setData(InstanceHashInterface::CODE, $code);
    }

    public function getCode(): string
    {
        return $this->_getData(InstanceHashInterface::CODE);
    }

    public function setValue(string $value): void
    {
        $this->setData(InstanceHashInterface::VALUE, $value);
    }

    public function getValue(): ?string
    {
        return $this->hasData(InstanceHashInterface::VALUE)
            ? (string)$this->_getData(InstanceHashInterface::VALUE)
            : null;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->setData(InstanceHashInterface::UPDATED_AT, $updatedAt);
    }

    public function getUpdatedAt(): string
    {
        return (string)$this->_getData(InstanceHashInterface::UPDATED_AT);
    }
}
