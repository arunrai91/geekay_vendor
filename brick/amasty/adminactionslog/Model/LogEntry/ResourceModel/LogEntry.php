<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\LogEntry\LogEntry as LogEntryModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LogEntry extends AbstractDb
{
    public const TABLE_NAME = 'amasty_audit_log_entry';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LogEntryModel::ID);
    }
}
