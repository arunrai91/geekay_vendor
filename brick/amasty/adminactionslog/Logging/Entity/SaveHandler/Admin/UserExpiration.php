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
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class UserExpiration extends Common
{
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Security\Model\UserExpiration $expiration */
        $expiration = $metadata->getObject();
        $type = $expiration->getOrigData() === [] ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __('Expiration for Admin #%1', $expiration->getUserId()),
            LogEntry::CATEGORY => 'admin/user/edit',
            LogEntry::CATEGORY_NAME => __('Admin Expiration'),
            LogEntry::ELEMENT_ID => (int)$expiration->getUserId(),
            LogEntry::PARAMETER_NAME => 'user_id'
        ];
    }

    public function processAfterSave($object): array
    {
        $object->load($object->getId());

        return parent::processAfterSave($object);
    }
}
