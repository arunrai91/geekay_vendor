<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\ResourceModel;

use Amasty\Xsearch\Model\QueryInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class UserSearch extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_xsearch_users_search';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    /**
     * @param int $days
     * @return void
     * @throws LocalizedException
     */
    public function deleteUserSearchOlderThan(int $days): void
    {
        $this->getConnection()
            ->delete(
                $this->getMainTable(),
                ['created_at < DATE_SUB(CURDATE(), INTERVAL ? DAY)' => $days]
            );
    }

    public function requestAnalyticsData(
        string $fromDate = null,
        string $toDate = null,
        string $dateFormat = null,
        int $limit = null
    ): ?array {
        $filters = $this->prepareDateFilter($fromDate, $toDate);

        return $this->fetchAnalitics(
            [
                QueryInfo::KEY_POPULARITY => 'COUNT(query_id)',
                QueryInfo::KEY_UNIQUE_QUERY => 'COUNT(DISTINCT query_id)',
                QueryInfo::KEY_UNIQUE_USER => 'COUNT(DISTINCT user_key)'
            ],
            $filters,
            $dateFormat,
            $limit
        );
    }

    public function getUniqueUsersTotal(
        string $fromDate = null,
        string $toDate = null
    ): int {
        $filters = $this->prepareDateFilter($fromDate, $toDate);

        $rows = $this->fetchAnalitics([QueryInfo::KEY_UNIQUE_USER => 'COUNT(DISTINCT user_key)'], $filters);
        $row = current($rows);
        if (!$row) {
            return 0;
        }

        return (int)$row[QueryInfo::KEY_UNIQUE_USER];
    }

    public function getProductClickUsers(
        string $fromDate = null,
        string $toDate = null,
        string $dateFormat = null,
        int $limit = null
    ): ?array {
        $filters = $this->prepareDateFilter($fromDate, $toDate);
        $filters[] = 'product_click IS NOT NULL';

        return $this->fetchAnalitics(
            [QueryInfo::KEY_PRODUCT_CLICK => 'COUNT(DISTINCT user_key)'],
            $filters,
            $dateFormat,
            $limit
        );
    }

    public function getProductClickUsersTotal(
        string $fromDate = null,
        string $toDate = null
    ): int {
        $rows = $this->getProductClickUsers($fromDate, $toDate);
        $row = current($rows);
        if (!$row) {
            return 0;
        }

        return (int)$row[QueryInfo::KEY_PRODUCT_CLICK];
    }

    public function getProductClickForQuery(int $queryId): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [QueryInfo::KEY_PRODUCT_CLICK => 'COUNT(DISTINCT user_key)'])
            ->where('query_id = ?', $queryId)
            ->where('product_click IS NOT NULL')
            ->limit(1);

        return (int)$this->getConnection()->fetchOne($select);
    }

    private function fetchAnalitics(
        array $columns,
        array $filters = [],
        string $dateFormat = null,
        int $limit = null
    ): ?array {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), $columns);

        foreach ($filters as $filter) {
            $select->where($filter);
        }
        if ($limit) {
            $select->limit($limit);
        }
        if ($dateFormat) {
            $dateColumn = sprintf('DATE_FORMAT(created_at, "%s")', $dateFormat);
            $select->group($dateColumn)
                ->columns(['created_at', 'date' => $dateColumn])
                ->order('created_at DESC');
        }

        return $this->getConnection()->fetchAll($select);
    }

    private function prepareDateFilter(?string $fromDate, ?string $toDate): array
    {
        $filters = [];
        if ($fromDate) {
            $filters[] = $this->getConnection()->quoteInto('created_at >= ?', $fromDate);
        }
        if ($toDate) {
            $filters[] = $this->getConnection()->quoteInto('created_at <= ?', $toDate);
        }

        return $filters;
    }
}
