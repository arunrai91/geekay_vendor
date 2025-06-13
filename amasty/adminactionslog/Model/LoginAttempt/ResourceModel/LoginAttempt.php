<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\ResourceModel;

use \Amasty\AdminActionsLog\Model\LoginAttempt\LoginAttempt as LoginAttemptModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LoginAttempt extends AbstractDb
{
    public const TABLE_NAME = 'amasty_audit_login_attempts';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LoginAttemptModel::ID);
    }
}
