<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\Grid;

use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    public const ADMIN_TABLE_ALIAS = 'amasty_admin_user';
    public const FULL_NAME_EXPR = "CONCAT(amasty_admin_user.firstname, ' ' ,amasty_admin_user.lastname)";

    protected function _construct()
    {
        $this->_init(Document::class, LogEntryResource::class);
        $this->setMainTable(LogEntryResource::TABLE_NAME);
        $this->addFilterToMap('full_name', new \Zend_Db_Expr(self::FULL_NAME_EXPR));
        $this->addFilterToMap('username', 'main_table.username');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            [self::ADMIN_TABLE_ALIAS => $this->getTable('admin_user')],
            'main_table.username = ' . self::ADMIN_TABLE_ALIAS . '.username',
            [
                'full_name' => new \Zend_Db_Expr(self::FULL_NAME_EXPR),
                'email' => self::ADMIN_TABLE_ALIAS . '.email',
            ]
        );

        return $this;
    }
}
