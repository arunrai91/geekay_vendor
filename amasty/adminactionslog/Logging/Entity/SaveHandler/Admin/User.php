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
use Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\User\Model\UserFactory;

class User extends Common
{
    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'reload_acl_flag',
        'modified'
    ];

    public function __construct(
        ArrayFilter\ScalarValueFilter $scalarValueFilter,
        ArrayFilter\KeyFilter $keyFilter,
        UserFactory $userFactory
    ) {
        parent::__construct($scalarValueFilter, $keyFilter);
        $this->userFactory = $userFactory;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\User\Model\User $adminUser */
        $adminUser = $metadata->getObject();

        if (!$adminUser->getUserName()) {
            $adminUser->load($adminUser->getId());
        }

        return [
            LogEntry::ITEM => $adminUser->getUserName(),
            LogEntry::CATEGORY => 'admin/user/edit',
            LogEntry::CATEGORY_NAME => __('Admin User'),
            LogEntry::ELEMENT_ID => (int)$adminUser->getId(),
            LogEntry::PARAMETER_NAME => 'user_id'
        ];
    }

    /**
     * @param \Magento\User\Model\User $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        if ($role = $object->getRole()) {
            $object->setOrigData('role_id', $role->getId());
        }

        return parent::processBeforeSave($object);
    }

    /**
     * @param \Magento\User\Model\User $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        $user = $this->userFactory->create()->load($object->getId());

        if ($role = $user->getRole()) {
            $user->setRoleId($role->getId());
        }

        return parent::processAfterSave($user);
    }
}
