<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter;

use Amasty\ElasticSearch\Model\Indexer\Data\Product\ProductDataMapper as AmastyElasticMapper;
use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper\StockStatus;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\DataMapper\ProductDataMapper as Elastic5ProductDataMapper;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;

class AdditionalBatchDataMapper
{
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers = [];

    public function __construct(array $dataMappers = [])
    {
        $this->dataMappers = $this->filterDataMappers($dataMappers);
    }

    /**
     * @param array $dataMappers
     * @return DataMapperInterface[]
     */
    private function filterDataMappers(array $dataMappers): array
    {
        return array_filter($dataMappers, function ($dataMapper) {
            return $dataMapper instanceof DataMapperInterface;
        });
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * Extension Amasty_Xsearch and Amasty_ElasticSearch map the data after Shopby and may lead to the data changes
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param AmastyElasticMapper|ProductDataMapper|Elastic5ProductDataMapper $subject
     * @param array $documentData
     * @param array $documentDataInput
     * @param int|string $storeId
     * @param array $context
     * @return array
     */
    public function afterMap(
        $subject,
        array $documentData,
        array $documentDataInput,
        $storeId,
        $context = []
    ): array {
        $productIds = array_keys($documentData);
        $storeId = (int) $storeId;
        $this->preloadCacheData($storeId, $productIds);
        foreach ($documentData as $productId => $document) {
            $context['document'] = $document;
            foreach ($this->getDataMappersForStore($storeId) as $mapper) {
                if (!isset($document[$mapper->getFieldName()])) {
                    // @codingStandardsIgnoreLine
                    $document = array_merge($document, $mapper->map($productId, $document, $storeId, $context));
                }
            }
            $documentData[$productId] = $document;
        }
        $this->clearCacheData($storeId);

        return $documentData;
    }

    /**
     * @return DataMapperInterface[]
     */
    private function getDataMappersForStore(int $storeId): array
    {
        return array_filter($this->dataMappers, function (DataMapperInterface $dataMapper) use ($storeId) {
            return $dataMapper->isAllowed($storeId);
        });
    }

    /**
     * @param int $storeId
     * @param int[] $productIds
     */
    private function preloadCacheData(int $storeId, array $productIds): void
    {
        /** @var DataMapperInterface|StockStatus $mapper */
        foreach ($this->getDataMappersForStore($storeId) as $mapper) {
            if (method_exists($mapper, 'preloadCacheData')) {
                $mapper->preloadCacheData($storeId, $productIds);
            }
        }
    }

    private function clearCacheData(int $storeId): void
    {
        /** @var DataMapperInterface|StockStatus $mapper */
        foreach ($this->getDataMappersForStore($storeId) as $mapper) {
            if (method_exists($mapper, 'preloadCacheData')) {
                $mapper->clearCacheData();
            }
        }
    }
}
