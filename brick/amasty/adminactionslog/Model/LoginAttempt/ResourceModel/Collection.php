<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel;

use Amasty\AdminActionsLog\Model\LoginAttempt\LoginAttempt as LoginAttemptModel;
use Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel\LoginAttempt as LoginAttemptResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(LoginAttemptModel::class, LoginAttemptResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
