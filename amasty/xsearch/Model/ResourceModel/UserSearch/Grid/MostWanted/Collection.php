<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\ResourceModel\UserSearch\Grid\MostWanted;

use Amasty\Xsearch\Model\ResourceModel\UserSearch;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    public const QUERY_TEXT = 'query_text';
    public const TOTAL_SEARCHES = 'total_searches';
    public const USERS_AMOUNT = 'users_amount';
    public const ENGAGEMENT = 'engagement';

    public const USER_ENGAGEMENT_TABLE = 'user_engagement';
    public const SEARCH_QUERY_TABLE = 'search_query';

    public const FILTER_AGGREGATION_PERIOD = 'aggregation_period';

    /**
     * @var Select
     */
    private $aggregationSelect;

    /**
     * @var Select
     */
    private $searchesByUserSelect;

    protected function _construct()
    {
        $this->_init(DataObject::class, UserSearch::class);
        $this->setMainTable(UserSearch::MAIN_TABLE);
    }

    protected function _renderOrders(): Collection
    {
        if (empty($this->_orders)) {
            $this->_orders[self::TOTAL_SEARCHES] = Select::SQL_DESC;
        }

        return parent::_renderOrders();
    }

    protected function _initSelect(): void
    {
        $select = $this->getAggregationSelect();
        $this->getSelect()->from(['main_table' => $select]);
    }

    public function getAggregationSelect(): Select
    {
        if ($this->aggregationSelect === null) {
            $this->aggregationSelect = $this->getConnection()->select();
            $this->aggregationSelect->from(['subquery_table' => $this->getMainTable()], [])
                ->columns([
                    self::QUERY_TEXT => sprintf('ps.%s', self::QUERY_TEXT),
                    self::TOTAL_SEARCHES => new \Zend_Db_Expr('COUNT(subquery_table.query_id)'),
                    self::USERS_AMOUNT => new \Zend_Db_Expr('COUNT(DISTINCT subquery_table.user_key)'),
                    self::ENGAGEMENT => sprintf('%s.%s', self::USER_ENGAGEMENT_TABLE, self::ENGAGEMENT)
                ])
                ->joinLeft(
                    ['ps' => $this->getTable(self::SEARCH_QUERY_TABLE)],
                    'subquery_table.query_id = ps.query_id',
                    []
                )
                ->joinInner(
                    [self::USER_ENGAGEMENT_TABLE => $this->getEngagementSelect()],
                    sprintf('subquery_table.query_id = %s.query_id', self::USER_ENGAGEMENT_TABLE),
                    []
                )
                ->where($this->_getConditionSql(sprintf('ps.%s', self::QUERY_TEXT), ['notnull' => true]))
                ->group('subquery_table.query_id');
        }

        return $this->aggregationSelect;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === self::FILTER_AGGREGATION_PERIOD) {
            $resultCondition = $this->_translateCondition('created_at', $condition);
            $this->aggregationSelect->where($resultCondition, null, Select::TYPE_CONDITION);
            $this->searchesByUserSelect->where($resultCondition, null, Select::TYPE_CONDITION);

            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    private function getEngagementSelect(): Select
    {
        $select = $this->getConnection()->select();
        $select->from(['sbct' => $this->getSearchesByUserSelect()]);
        $select->reset(Select::COLUMNS);
        $select->columns([
            'query_id' => 'sbct.query_id',
            self::ENGAGEMENT => new \Zend_Db_Expr('ROUND(sum(is_clicked) / count(query_id) * 100, 2)')
        ]);
        $select->group('sbct.query_id');

        return $select;
    }

    public function getSearchesByUserSelect(): Select
    {
        if ($this->searchesByUserSelect === null) {
            $this->searchesByUserSelect = $this->getConnection()->select();
            $this->searchesByUserSelect->from(['sbu' => $this->getMainTable()])
                ->reset(Select::COLUMNS)
                ->columns([
                    'query_id',
                    'is_clicked' => new \Zend_Db_Expr('if(count(sbu.product_click) > 0, 1, 0)')
                ])
                ->group(['query_id', 'user_key']);
        }

        return $this->searchesByUserSelect;
    }
}
