<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceData;

use Amasty\Base\Api\Data\InstanceDataInterface;
use Magento\Framework\Model\AbstractModel;

class InstanceData extends AbstractModel implements InstanceDataInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\InstanceData::class);
        $this->setIdFieldName(InstanceDataInterface::ID);
    }

    public function getId(): ?int
    {
        return $this->hasData(InstanceDataInterface::ID)
            ? (int)$this->_getData(InstanceDataInterface::ID)
            : null;
    }

    public function setCode(string $code): void
    {
        $this->setData(InstanceDataInterface::CODE, $code);
    }

    public function getCode(): string
    {
        return $this->_getData(InstanceDataInterface::CODE);
    }

    public function setValue(string $value): void
    {
        $this->setData(InstanceDataInterface::VALUE, $value);
    }

    public function getValue(): ?string
    {
        return $this->hasData(InstanceDataInterface::VALUE)
            ? (string)$this->_getData(InstanceDataInterface::VALUE)
            : null;
    }
}
