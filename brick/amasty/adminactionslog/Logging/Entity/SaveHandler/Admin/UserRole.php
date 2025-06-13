<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Admin;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class UserRole extends Common
{
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Authorization\Model\Role $role */
        $role = $metadata->getObject();

        return [
            LogEntry::ITEM => $role->getRoleName(),
            LogEntry::CATEGORY => 'admin/user_role/editrole',
            LogEntry::CATEGORY_NAME => __('Admin User Role'),
            LogEntry::ELEMENT_ID => (int)$role->getId(),
            LogEntry::PARAMETER_NAME => 'rid'
        ];
    }
}
