<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Schedule\ResourceModel;

use Amasty\Base\Api\Data\ScheduleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Schedule extends AbstractDb
{
    public const TABLE_NAME = 'amasty_base_schedule';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ScheduleInterface::ID);
    }
}
