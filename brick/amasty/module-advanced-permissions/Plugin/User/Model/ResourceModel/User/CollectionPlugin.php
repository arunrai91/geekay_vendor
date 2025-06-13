<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\User\Model\ResourceModel\User;

use Magento\Framework\DB\Select;

class CollectionPlugin
{
    /**
     * @var \Amasty\Rolepermissions\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    public function beforeLoad(
        \Magento\User\Model\ResourceModel\User\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $currentRule = $this->helper->currentRule();
        if (!$this->registry->registry('its_amrolepermissions')
            && $currentRule
            && $currentRule->getRoles()
        ) {
            $alias = $this->getMainAlias($subject->getSelect()) ? $this->getMainAlias($subject->getSelect()) . '.' : '';
            $allowedUsers = $currentRule->getAllowedUsers();
            $subject->addFieldToFilter($alias . 'user_id', ['in' => $allowedUsers]);
        }

        return [$printQuery, $logQuery];
    }

    /**
     * @param Select $select
     * @return bool|int|string
     * @throws \Zend_Db_Select_Exception
     */
    protected function getMainAlias(Select $select)
    {
        $from = $select->getPart(Select::FROM);

        foreach ($from as $alias => $data) {
            if ($data['joinType'] == 'from') {
                return $alias;
            }
        }

        return false;
    }
}
