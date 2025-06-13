<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\InstanceHash\ResourceModel;

use Amasty\Base\Api\Data\InstanceHashInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InstanceHash extends AbstractDb
{
    public const TABLE_NAME = 'amasty_base_instance_hash';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, InstanceHashInterface::ID);
    }
}
