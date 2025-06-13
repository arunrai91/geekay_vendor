<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\ResourceModel\UserSearch;

use Amasty\Xsearch\Model\QueryInfo;
use Magento\Framework\DB\Select;

/**
 * @method \Amasty\Xsearch\Model\ResourceModel\UserSearch getResource()
 * @method \Amasty\Xsearch\Model\UserSearch[] getItems()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public const GROUP_BY_MONTH_PATTERN = '%Y%m';
    public const GROUP_BY_DAY_PATTERN = '%Y%m%d';
    public const LIMIT_LAST_DATA = 10;

    protected function _construct()
    {
        $this->_init(\Amasty\Xsearch\Model\UserSearch::class, \Amasty\Xsearch\Model\ResourceModel\UserSearch::class);
    }

    /**
     * @param string $groupBy
     * @return $this
     *
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::requestAnalyticsData
     */
    public function getPopularity($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)->columns(['COUNT(query_id) as popularity']);
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     *
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::requestAnalyticsData
     */
    public function getUniqueSearch($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)->columns('COUNT(DISTINCT query_id) as unique_query');
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::requestAnalyticsData
     */
    public function getUniqueUsers($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)->columns('COUNT(DISTINCT user_key) as unique_user');
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::getProductClickUsers
     */
    public function getProductClickUsers($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)
            ->columns(['COUNT(DISTINCT user_key) as product_click'])
            ->where('product_click IS NOT NULL');
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     */
    private function groupByDate($groupBy)
    {
        if ($groupBy) {
            $this->getSelect()
                ->group(sprintf('DATE_FORMAT(created_at, "%s")', $groupBy))
                ->columns('created_at')
                ->order('created_at DESC');
        }
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::requestAnalyticsData
     * @return int
     */
    public function getTotalRowPopularity()
    {
        return (int)current($this->getResource()->requestAnalyticsData())[QueryInfo::KEY_POPULARITY];
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::requestAnalyticsData
     * @return int
     */
    public function getTotalRowUniqueQuery()
    {
        return (int)current($this->getResource()->requestAnalyticsData())[QueryInfo::KEY_UNIQUE_QUERY];
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::requestAnalyticsData
     * @return int
     */
    public function getTotalRowUniqueUsers()
    {
        return (int)current($this->getResource()->requestAnalyticsData())[QueryInfo::KEY_UNIQUE_USER];
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::getProductClickUsers
     * @return int
     */
    public function getTotalRowProductClick()
    {
        return current(current($this->getResource()->getProductClickUsers()));
    }

    /**
     * @return $this
     */
    public function getSearchQueries($limit)
    {
        $this->getSelect()->joinLeft(
            ['query' => $this->getTable('search_query')],
            'query.query_id = main_table.query_id'
        )
            ->reset(Select::COLUMNS)
            ->columns([
                'query.query_text',
                'COUNT(query.query_text) as total_searches',
                'COUNT(DISTINCT user_key) as user_key',
                'query.query_id'
            ])
            ->where('query_text IS NOT NULL')
            ->group('query.query_text')
            ->order('total_searches DESC')
            ->limit($limit);

        return $this;
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\ResourceModel\UserSearch::getProductClickForQuery
     */
    public function getProductClickForQuery($queryId)
    {
        return (int)$this->getResource()->getProductClickForQuery($queryId);
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->getSelect()->limit($limit);

        return $this;
    }
}
