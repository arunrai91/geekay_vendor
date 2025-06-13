<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperResolverInterface;

class DataMapperResolver implements DataMapperResolverInterface
{
    public const DEFAULT_DATA_INDEXER = 'catalogsearch_fulltext';
    public const INDEXER_ID = 'indexer_id';

    /**
     * @since 1.17.1 added mapped data to context
     */
    public const MAPPED_DATA_KEY = 'document';

    /**
     * @var DataMapperInterface[][]
     */
    private $mappers = [];

    public function __construct(array $dataMappers = [])
    {
        foreach ($dataMappers as $type => $mappers) {
            $this->mappers[$type] = [];
            foreach ($mappers as $mapper) {
                $this->mappers[$type][] = $mapper;
            }
        }
    }

    /**
     * @param array $indexData [attribute_id(int) => attribute_value(string|int|string[]|int[])]
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function mapEntityData(array $indexData, $storeId, $context = [])
    {
        $data = [];
        $indexerId = $context[self::INDEXER_ID] ?? self::DEFAULT_DATA_INDEXER;
        if (isset($this->mappers[$indexerId]) && is_array($this->mappers[$indexerId])) {
            /** @var DataMapperInterface $mapper */
            foreach ($this->mappers[$indexerId] as $mapper) {
                $context[self::MAPPED_DATA_KEY] = $data;
                $mappedData = $mapper->map($indexData, $storeId, $context);
                $data = $this->mergeData($mappedData, $data);
            }
        }
        return $data;
    }

    private function mergeData(array $mappedData, array $data): array
    {
        /** @var array $entityData */
        foreach ($mappedData as $entityId => $entityData) {
            if (isset($data[$entityId])) {
                $data[$entityId] = $entityData + $data[$entityId];
            } else {
                $data[$entityId] = $entityData;
            }
        }

        return $data;
    }
}
