<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceHash\ResourceModel;

use Amasty\Base\Model\InstanceHash\InstanceHash as InstanceHashModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(InstanceHashModel::class, InstanceHash::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
