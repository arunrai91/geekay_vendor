<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Amasty\ElasticSearch\Model\Search\GetResponse\GetAggregations;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface;

class Term
{
    /**
     * @var BucketBuilderInterface[]
     */
    private $bucketBuilders = [];

    /**
     * SearchAdapterTermAddDataPlugin constructor.
     * @param array $bucketBuilders
     */
    public function __construct(array $bucketBuilders = [])
    {
        $this->bucketBuilders = $bucketBuilders;
    }

    /**
     * @param GetAggregations $subject
     * @param \Closure $closure
     * @param RequestBucketInterface $bucket
     * @param array $elasticResponse
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTermBucket(
        GetAggregations $subject, // @phpstan-ignore class.notFound
        \Closure $closure,
        RequestBucketInterface $bucket,
        array $elasticResponse
    ) {
        $builtCustomFilter = $this->buildCustomFiltersData($bucket, $elasticResponse);
        return $builtCustomFilter ?: $closure($bucket, $elasticResponse);
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        $subject,
        \Closure $closure,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        $builtCustomFilter = $this->buildCustomFiltersData($bucket, $queryResult);
        return $builtCustomFilter ?: $closure($bucket, $dimensions, $queryResult, $dataProvider);
    }

    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array|void
     */
    private function buildCustomFiltersData(RequestBucketInterface $bucket, array $queryResult)
    {
        if (isset($this->bucketBuilders[$bucket->getField()])) {
            $builder = $this->bucketBuilders[$bucket->getField()];
            if ($builder instanceof BucketBuilderInterface) {
                return $builder->build($bucket, $queryResult);
            }
        }
    }
}
