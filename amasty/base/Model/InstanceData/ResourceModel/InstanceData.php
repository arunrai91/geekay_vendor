<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceData\ResourceModel;

use Amasty\Base\Api\Data\InstanceDataInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InstanceData extends AbstractDb
{
    public const TABLE_NAME = 'amasty_base_instance_data';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, InstanceDataInterface::ID);
    }
}
