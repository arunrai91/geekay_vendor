<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\ResourceModel;

use Amasty\Acart\Model\Schedule as ScheduleModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Schedule extends AbstractDb
{
    public const TABLE_NAME = 'amasty_acart_schedule';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ScheduleModel::SCHEDULE_ID);
    }
}
