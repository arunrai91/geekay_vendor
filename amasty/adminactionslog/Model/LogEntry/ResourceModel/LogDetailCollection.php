<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\LogEntry\LogDetail as LogDetailModel;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail as LogDetailResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class LogDetailCollection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(LogDetailModel::class, LogDetailResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
