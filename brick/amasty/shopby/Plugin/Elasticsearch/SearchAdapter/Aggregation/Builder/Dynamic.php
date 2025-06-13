<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Amasty\ElasticSearch\Model\Search\GetResponse\GetAggregations;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic as DynamicBuilder;

class Dynamic
{
    /**
     * @param GetAggregations $subject
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $elasticResponse
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDynamicBucket(
        GetAggregations $subject, // @phpstan-ignore class.notFound
        array $resultData,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $elasticResponse
    ) {
        return $this->addSliderData($resultData, $elasticResponse['aggregations'][$bucket->getName()]);
    }

    /**
     * @param DynamicBuilder|\Mirasvit\SearchElastic\Adapter\Aggregation\DynamicBucket $builder
     * @param array $resultData
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(
        $builder,
        array $resultData,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        return $this->addSliderData($resultData, $queryResult['aggregations'][$bucket->getName()] ?? null);
    }

    /**
     * Used in a price and decimal sliders.
     *
     * @param array $resultData
     * @param array $aggregation
     * @return array
     */
    private function addSliderData(array $resultData, ?array $aggregation)
    {
        if ($aggregation && isset($aggregation['min'])) {
            $resultData['data']['value'] = 'data';
            $resultData['data']['min'] = $aggregation['min'];
            $resultData['data']['max'] = $aggregation['max'];
            $resultData['data']['count'] = $aggregation['count'];
        }

        return $resultData;
    }
}
