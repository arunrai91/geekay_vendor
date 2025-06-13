<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model;

use Amasty\Xsearch\Model\Report\DataFilterProvider;
use Amasty\Xsearch\Model\ResourceModel\UserSearch as UserSearchResource;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class QueryInfo
{
    public const MONTHS = 12;

    public const LIMIT_ROWS = 10;

    public const KEY_UNIQUE_QUERY = 'unique_query';

    public const KEY_POPULARITY = 'popularity';

    public const KEY_PRODUCT_CLICK = 'product_click';

    public const KEY_UNIQUE_USER = 'unique_user';

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ResourceModel\UserSearch\CollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var ResourceModel\UserSearch
     */
    private $searchResource;

    /**
     * @var Report\DataFilterProvider
     */
    private $dataFilterProvider;

    public function __construct(
        DateTime $dateTime,
        DataObjectFactory $dataObjectFactory,
        CollectionFactory $queryCollectionFactory,
        UserSearchResource $searchResource,
        DataFilterProvider $dataFilterProvider
    ) {
        $this->dateTime = $dateTime;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->searchResource = $searchResource;
        $this->dataFilterProvider = $dataFilterProvider;
    }

    /**
     * @param string $timePeriod
     * @param bool $isNeedLimit
     * @return array
     */
    public function getAnalyticsData($timePeriod, $isNeedLimit = true)
    {
        $isMonthPeriod = $timePeriod == Collection::GROUP_BY_MONTH_PATTERN;
        $this->dateFormat = $isMonthPeriod ? 'F Y' : 'd F Y';
        if ($isMonthPeriod) {
            $this->limit = self::MONTHS;
        } else {
            $this->limit = $isNeedLimit ? self::LIMIT_ROWS : null;
        }

        $fromDate = $this->dataFilterProvider->getFromDate();
        $toDate = $this->dataFilterProvider->getToDate();
        $analyticsConfig = $this->searchResource->requestAnalyticsData($fromDate, $toDate, $timePeriod, $this->limit);
        $this->getProductClick($timePeriod, $analyticsConfig);
        foreach ($analyticsConfig as &$item) {
            $item['created_at'] = $this->dateTime->date($this->dateFormat, $item['created_at']);
        }

        return $isMonthPeriod ? array_reverse($analyticsConfig) : $analyticsConfig;
    }

    /**
     * @param string $timePeriod
     * @param array $analyticsConfig
     */
    private function getProductClick($timePeriod, &$analyticsConfig)
    {
        $productClickUsers = $this->searchResource->getProductClickUsers(
            $this->dataFilterProvider->getFromDate(),
            $this->dataFilterProvider->getToDate(),
            $timePeriod,
            $this->limit
        );
        foreach ($productClickUsers as $user) {
            foreach ($analyticsConfig as $key => $item) {
                if ($user['date'] === $item['date']) {
                    $percentClick = round(($user['product_click'] / $item['unique_user']) * 100, 2);
                    $analyticsConfig[$key]['product_click'] = $percentClick;
                    continue 2;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getMostWantedQueries($limit = null, $curPage = 1)
    {
        /** @var Collection $searchQueries */
        $searchQueries = $this->queryCollectionFactory->create();
        $searchQueries->setCurPage($curPage)->setPageSize($limit)->getSearchQueries($limit);

        $this->addFromToFilter($searchQueries);

        $result['totalRecords'] = $searchQueries->getSize();
        $result['items'] = [];
        foreach ($searchQueries->getData() as $key => $queryData) {
            $clickCount = $this->searchResource->getProductClickForQuery((int)$queryData['query_id']);
            $clicks = $clickCount ? round(($clickCount / $queryData['user_key']) * 100, 2) : 0;
            $result['items'][$key] = $this->dataObjectFactory->create(
                [
                    'data' => [
                        'product_click' => $clicks,
                        'query_text' => $queryData['query_text'],
                        'total_searches' => $queryData['total_searches'],
                        'user_key' => $queryData['user_key']
                    ]
                ]
            );
        }

        return $result;
    }

    /**
     * @param Collection $searchQueries
     */
    private function addFromToFilter(AbstractDb $searchQueries): void
    {
        $filters = [];
        if ($fromDate = $this->dataFilterProvider->getFromDate()) {
            $filters['from'] = $fromDate;
        }
        if ($toDate = $this->dataFilterProvider->getToDate()) {
            $filters['to'] = $toDate;
        }

        if (!empty($filters)) {
            $searchQueries->addFieldToFilter('created_at', $filters);
        }
    }
}
