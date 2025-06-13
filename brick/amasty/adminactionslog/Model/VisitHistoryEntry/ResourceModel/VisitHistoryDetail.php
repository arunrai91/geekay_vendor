<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryDetail as VisitHistoryDetailModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VisitHistoryDetail extends AbstractDb
{
    public const TABLE_NAME = 'amasty_audit_visit_details';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, VisitHistoryDetailModel::ID);
    }
}
