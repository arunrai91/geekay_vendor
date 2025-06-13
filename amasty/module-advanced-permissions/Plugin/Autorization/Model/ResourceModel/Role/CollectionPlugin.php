<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Autorization\Model\ResourceModel\Role;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\User;
use Magento\Framework\Registry;

class CollectionPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * CollectionPlugin constructor.
     *
     * @param Data $helper
     * @param Registry $registry
     */
    public function __construct(
        Data $helper,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    public function beforeLoad(
        \Magento\Authorization\Model\ResourceModel\Role\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $currentRule = $this->helper->currentRule();
        if (!$this->registry->registry('its_amrolepermissions')
            && $currentRule
            && $currentRule->getRoles()
        ) {
            $roles = $currentRule->getRoles();

            $roleType = $subject->getConnection()->quoteInto('main_table.role_type = ?', RoleGroup::ROLE_TYPE);
            $roleId = $subject->getConnection()->quoteInto('main_table.role_id NOT IN (?)', $roles);
            $condition = new \Zend_Db_Expr($roleType . ' AND ' . $roleId);
            $fullCondition = $subject->getConnection()->getCheckSql($condition, 0, 1);

            $roleType = $subject->getConnection()->quoteInto('main_table.role_type = ?', User::ROLE_TYPE);
            $roleId = $subject->getConnection()->quoteInto('main_table.parent_id NOT IN (?)', $roles);
            $condition = new \Zend_Db_Expr($roleType . ' AND ' . $roleId);
            $fullCondition .= ' AND ' . $subject->getConnection()->getCheckSql($condition, 0, 1);

            $subject->getSelect()->where($fullCondition);
        }

        return [$printQuery, $logQuery];
    }
}
