<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceData\ResourceModel;

use Amasty\Base\Model\InstanceData\InstanceData as InstanceDataModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(InstanceDataModel::class, InstanceData::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
