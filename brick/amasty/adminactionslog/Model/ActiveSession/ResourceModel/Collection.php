<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession as ActiveSessionModel;
use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(ActiveSessionModel::class, ActiveSessionResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
