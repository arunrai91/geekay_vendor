<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry as VisitHistoryEntryResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryEntry as VisitHistoryEntryModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(VisitHistoryEntryModel::class, VisitHistoryEntryResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
