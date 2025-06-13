<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Search;

use Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface;
use Amasty\ElasticSearch\Model\Indexer\Data\External\RelevanceBoostDataMapper;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\InjectSubqueryInterface;
use Amasty\ElasticSearch\Model\Search\GetRequestQuery\SortingProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;

class GetRequestQuery
{
    public const QUERY_CONDITION_MUST_NOT = 'must_not';

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var GetRequestQuery\GetAggregations
     */
    private $getAggregations;

    /**
     * @var InjectSubqueryInterface[]
     */
    private $subqueryInjectors;

    /**
     * @var SortingProvider
     */
    private $sortingProvider;

    /**
     * @var array
     */
    private $requestNamesForApplyRelevanceRules;

    /**
     * @var string[]
     */
    private $booleanMethods = [
        BoolQuery::QUERY_CONDITION_MUST => 'getMust',
        BoolQuery::QUERY_CONDITION_SHOULD => 'getShould',
        self::QUERY_CONDITION_MUST_NOT => 'getMustNot'
    ];

    /**
     * @var RelevanceRuleRepositoryInterface
     */
    private $relevanceRuleRepository;

    public function __construct(
        \Amasty\ElasticSearch\Model\Config $config,
        GetRequestQuery\GetAggregations $getAggregations,
        SortingProvider $sortingProvider,
        RelevanceRuleRepositoryInterface $relevanceRuleRepository,
        array $subqueryInjectors,
        array $requestNamesForApplyRelevanceRules = []
    ) {
        $this->config = $config;
        $this->subqueryInjectors = $subqueryInjectors;
        $this->getAggregations = $getAggregations;
        $this->sortingProvider = $sortingProvider;
        $this->requestNamesForApplyRelevanceRules = array_unique($requestNamesForApplyRelevanceRules);
        $this->relevanceRuleRepository = $relevanceRuleRepository;
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws LocalizedException
     */
    public function execute(RequestInterface $request): array
    {
        $storeId = (int) current($request->getDimensions())->getValue();
        $query = [
            'index' => $this->config->getIndexName('product', $storeId),
            'type' => $this->config->getEntityType(),
            'body' => [
                'from' => $request->getFrom(),
                'size' => $request->getSize(),
                'stored_fields' => ['_id', '_score'],
                'sort' => $this->sortingProvider->execute($request),
                'query' => $this->getQuery($request)
            ],
        ];
        $aggregations = $this->getAggregations->execute($request);
        if ($aggregations) {
            $query['body']['aggregations'] = $aggregations;
        }

        if (isset($query['body']['query']['script_score']['query']['bool']['should'])) {
            $query['body']['query']['script_score']['query']['bool']['minimum_should_match'] = 1;
        }
        if (isset($query['body']['query']['bool']['should'])) {
            $query['body']['query']['bool']['minimum_should_match'] = 1;
        }

        $query['track_total_hits'] = true;

        return $query;
    }

    private function getQuery(RequestInterface $request): array
    {
        if ($this->isCanApplyRelevanceSorting($request) && $this->isNeedApplyRelevanceSorting()) {
            $query = [
                'script_score' => [
                    'query' => $this->processQuery($request->getQuery(), [], BoolQuery::QUERY_CONDITION_MUST),
                    'script' => [
                        'source' => "params['_source']['" . RelevanceBoostDataMapper::ATTRIBUTE_NAME . "']*_score"
                    ]
                ]
            ];
        } else {
            $query = $this->processQuery($request->getQuery(), [], BoolQuery::QUERY_CONDITION_MUST);
        }

        return $query;
    }

    public function isCanApplyRelevanceSorting(RequestInterface $request): bool
    {
        return in_array($request->getName(), $this->requestNamesForApplyRelevanceRules);
    }

    private function isNeedApplyRelevanceSorting(): bool
    {
        return $this->relevanceRuleRepository->getActiveRules()->getSize() !== 0;
    }

    /**
     * @param QueryInterface $request
     * @param array $elasticQuery
     * @param $conditionType
     * @return array
     * @throws LocalizedException
     */
    private function processQuery(QueryInterface $request, array $elasticQuery, $conditionType)
    {
        switch ($request->getType()) {
            case QueryInterface::TYPE_BOOL:
                /** @var BoolQuery $request */
                foreach ($this->booleanMethods as $conditionType => $method) {
                    foreach ($request->{$method}() as $subQuery) {
                        $elasticQuery = $this->processQuery($subQuery, $elasticQuery, $conditionType);
                    }
                }
                break;
            case QueryInterface::TYPE_MATCH:
                /** @var MatchQuery $request */
                $addSubquery = $this->getSubqueryInjectorByType('match');
                $elasticQuery = $addSubquery->execute($elasticQuery, $request, $conditionType);
                break;
            case QueryInterface::TYPE_FILTER:
                /** @var FilterQuery $request */
                if ($request->getReferenceType() === FilterQuery::REFERENCE_QUERY) {
                    $elasticQuery = $this->processQuery($request->getReference(), $elasticQuery, $conditionType);
                } elseif ($request->getReferenceType() === FilterQuery::REFERENCE_FILTER) {
                    $addSubquery = $this->getSubqueryInjectorByType($request->getReference()->getType());
                    $elasticQuery = $addSubquery->execute($elasticQuery, $request, $conditionType);
                } else {
                    throw new LocalizedException(
                        __("Builder for %1 reference type doesn't exist", $request->getReferenceType())
                    );
                }

                break;
            default:
                throw new LocalizedException(__("Processor for %1 type doesn't exist", $request->getType()));
        }

        return $elasticQuery;
    }

    /**
     * @param string $filterType
     * @return InjectSubqueryInterface
     * @throws LocalizedException
     */
    private function getSubqueryInjectorByType($filterType)
    {
        if (!array_key_exists($filterType, $this->subqueryInjectors)) {
            throw new LocalizedException(__("'%1' filter type is not supported", $filterType));
        }

        return $this->subqueryInjectors[$filterType];
    }
}
