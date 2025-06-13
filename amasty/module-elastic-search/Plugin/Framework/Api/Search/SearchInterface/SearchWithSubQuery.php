<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Plugin\Framework\Api\Search\SearchInterface;

use Amasty\ElasticSearch\Model\Search\SubQuery\Helper as SubQueryHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

class SearchWithSubQuery
{
    public const PRODUCTS_LIMIT_MAIN_QUERY = 9000;

    private const SEARCH_TERM_FILTER_CODE = 'search_term';

    /**
     * @var SubQueryHelper
     */
    private $subQueryHelper;

    /**
     * @var null|int
     */
    private $originalCurrentPage;

    /**
     * @var null|int
     */
    private $originalPageSize;

    /**
     * @var bool
     */
    private $isSubQuerySearch = false;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        SubQueryHelper $subQueryHelper,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->subQueryHelper = $subQueryHelper;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSearch(SearchInterface $search, SearchCriteriaInterface $searchCriteria): array
    {
        if ($this->isSubQuerySearchAllowed() && !$this->isSubQuerySearch) {
            $this->originalCurrentPage = $searchCriteria->getCurrentPage();
            $this->originalPageSize = $searchCriteria->getPageSize();
            $searchCriteria->setCurrentPage(0);
            $searchCriteria->setPageSize(static::PRODUCTS_LIMIT_MAIN_QUERY);
        }

        return [$searchCriteria];
    }

    public function afterSearch(
        SearchInterface $search,
        SearchResultInterface $searchResult,
        SearchCriteriaInterface $searchCriteria
    ): SearchResultInterface {
        if ($this->isSubQuerySearchAllowed() && !$this->isSubQuerySearch) {
            $this->isSubQuerySearch = true;
            $entityIds = $this->getEntityIds($searchResult);
            if (empty($entityIds)) {
                return $searchResult;
            }

            $this->modifySearchTerm($searchCriteria);
            $this->applyEntityIdsFilter($searchCriteria, $entityIds);
            $searchResult = $search->search($searchCriteria);
            $this->isSubQuerySearch = false;
        }

        return $searchResult;
    }

    private function isSubQuerySearchAllowed(): bool
    {
        return $this->subQueryHelper->getQueryText() !== null;
    }

    private function modifySearchTerm(SearchCriteriaInterface $searchCriteria): void
    {
        $searchCriteria->setCurrentPage($this->originalCurrentPage);
        $searchCriteria->setPageSize($this->originalPageSize);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === self::SEARCH_TERM_FILTER_CODE) {
                    $filter->setValue($this->subQueryHelper->getQueryText());
                }
            }
        }
    }

    /**
     * Add entity ids filter for $searchCriteria.
     */
    private function applyEntityIdsFilter(
        SearchCriteriaInterface $searchCriteria,
        array $entityIds
    ): void {
        $this->filterBuilder->setField('amasty_entity_ids');
        $this->filterBuilder->setValue($entityIds);
        $this->filterBuilder->setConditionType('in');

        $this->filterGroupBuilder->setFilters([]);
        $this->filterGroupBuilder->addFilter($this->filterBuilder->create());
        $filterGroup = $this->filterGroupBuilder->create();

        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $filterGroup;
        $searchCriteria->setFilterGroups($filterGroups);
    }

    /**
     * Retrieve entity ids from search result.
     */
    private function getEntityIds(SearchResultInterface $searchResult): array
    {
        return array_map(function (DocumentInterface $item) {
            return $item->getId();
        }, $searchResult->getItems());
    }
}
