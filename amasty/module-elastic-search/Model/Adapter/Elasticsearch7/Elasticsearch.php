<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adapter\Elasticsearch7;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperResolverInterface;
use Amasty\ElasticSearch\Api\Data\Indexer\Structure\IndexBuilderInterface;
use Amasty\ElasticSearch\Model\Adapter\AdapterInterface;
use Amasty\ElasticSearch\Model\Adapter\FieldMappingBuilderInterface;
use Amasty\ElasticSearch\Model\Adapter\Index\IndexNameResolver;
use Amasty\ElasticSearch\Model\Adapter\SaveQueryBuilderInterface;
use Amasty\ElasticSearch\Model\Client\ClientInterface;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Amasty\ElasticSearch\Model\Debug;
use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder as EntityStructureBuilder;
use Exception;

class Elasticsearch implements AdapterInterface
{
    /**
     * @var FieldMappingBuilderInterface
     */
    private $fieldMappingBuilder;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var IndexBuilderInterface
     */
    private $indexBuilder;

    /**
     * @var EntityStructureBuilder
     */
    private $entityStructureBuilder;

    /**
     * @var DataMapperResolverInterface
     */
    private $dataMapperResolver;

    /**
     * @var SaveQueryBuilderInterface
     */
    private $saveQueryBuilder;

    /**
     * @var Debug
     */
    private $debug;

    /**
     * @var ClientInterface|null
     */
    private $client;

    /**
     * @var string[] ['store_id' => 'index_name']
     */
    private $indexNameByStore = [];

    public function __construct(
        IndexNameResolver $indexNameResolver,
        ClientRepositoryInterface $clientRepository,
        IndexBuilderInterface $indexBuilder,
        EntityStructureBuilder $entityStructureBuilder,
        FieldMappingBuilderInterface $fieldMappingBuilder,
        DataMapperResolverInterface $dataMapperResolver,
        SaveQueryBuilderInterface $saveQueryBuilder,
        Debug $debug,
        ?ClientInterface $client = null
    ) {
        $this->indexNameResolver = $indexNameResolver;
        $this->clientRepository = $clientRepository;
        $this->indexBuilder = $indexBuilder;
        $this->entityStructureBuilder = $entityStructureBuilder;
        $this->fieldMappingBuilder = $fieldMappingBuilder;
        $this->dataMapperResolver = $dataMapperResolver;
        $this->saveQueryBuilder = $saveQueryBuilder;
        $this->debug = $debug;
        $this->client = $client;
    }

    public function prepareDocuments(array $documentData, int $storeId, string $indexerId): array
    {
        $documents = [];
        if (!empty($documentData)) {
            $documents = $this->dataMapperResolver->mapEntityData(
                $documentData,
                $storeId,
                ['indexer_id' => $indexerId]
            );
        }

        return $documents;
    }

    public function saveDocuments(array $documents, int $storeId, string $indexerId): void
    {
        if (!empty($documents)) {
            try {
                $indexName = $this->indexNameResolver->getIndexName($indexerId, $storeId, $this->indexNameByStore);
                if ($this->getClient()->indexExists($indexName)) {
                    $query = $this->saveQueryBuilder->execute($documents, $indexName);
                    $this->getClient()->bulkQuery($query);
                }
            } catch (Exception $e) {
                $this->debug->debug($e);
                throw $e;
            }
        }
    }

    public function deleteDocuments(array $documentIds, int $storeId, string $indexerId): void
    {
        try {
            $indexName = $this->indexNameResolver->getIndexName($indexerId, $storeId, $this->indexNameByStore);
            if (!$this->getClient()->indexExists($indexName)) {
                return;
            }

            $query = $this->saveQueryBuilder->execute(
                $documentIds,
                $indexName,
                SaveQueryBuilderInterface::BULK_ACTION_DELETE
            );
            $this->getClient()->bulkQuery($query);
        } catch (Exception $e) {
            $this->debug->debug($e);
            throw $e;
        }
    }

    public function cleanIndex(int $storeId, string $indexerId): void
    {
        // needed to fix bug with double indices in alias because of second reindex in same process
        unset($this->indexNameByStore[$storeId]);

        $this->checkIndex($storeId, $indexerId);
        $indexName = $this->indexNameResolver->getIndexName($indexerId, $storeId, $this->indexNameByStore);
        if ($this->getClient()->isEmptyIndex($indexName)) {
            return;
        }

        $indexPattern = $this->indexNameResolver->getIndexAlias($indexerId, $storeId) . '_v';
        $version = (int)(str_replace($indexPattern, '', $indexName));
        $newIndexName = $indexPattern . (++$version);

        if ($this->getClient()->indexExists($newIndexName)) {
            $this->getClient()->deleteIndex($newIndexName);
        }
        $this->prepareIndex($storeId, $newIndexName, $indexerId);
    }

    public function checkIndex(int $storeId, string $indexerId): void
    {
        $indexName = $this->indexNameResolver->getIndexName($indexerId, $storeId, $this->indexNameByStore);
        if (!$this->getClient()->indexExists($indexName)) {
            $this->prepareIndex($storeId, $indexName, $indexerId);
        }

        $alias = $this->indexNameResolver->getIndexAlias($indexerId, $storeId);
        if (!$this->getClient()->existsAlias($alias, $indexName)) {
            $this->getClient()->updateAlias($alias, $indexName);
        }
    }

    public function updateAlias(int $storeId, string $indexerId): void
    {
        if (!isset($this->indexNameByStore[$storeId])) {
            return;
        }

        $alias = $this->indexNameResolver->getIndexAlias($indexerId, $storeId);
        $oldIndex = $this->indexNameResolver->getIndexNameByAlias($alias, $storeId, $this->indexNameByStore);

        if (!empty($oldIndex)) {
            $params['body']['actions'][] = ['remove' => ['alias' => $alias, 'index' => $oldIndex]];
        }
        $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $this->indexNameByStore[$storeId]]];

        $this->getClient()->updateAliases($params);

        if ($oldIndex) {
            $this->getClient()->deleteIndex($oldIndex);
        }
    }

    public function ping(): bool
    {
        return $this->getClient()->ping();
    }

    private function prepareIndex(int $storeId, string $indexName, string $indexerId): void
    {
        $this->indexBuilder->setStoreId($storeId);
        $this->getClient()->createIndex($indexName, $this->indexBuilder->build());
        $mappingData = $this->fieldMappingBuilder->execute(
            $indexName,
            $storeId,
            $this->entityStructureBuilder->build($indexerId)
        );
        $this->getClient()->putMapping($mappingData);

        $this->indexNameByStore[$storeId] = $indexName;
    }

    private function getClient(): ClientInterface
    {
        if ($this->client === null) {
            $this->client = $this->clientRepository->get();
        }
        return $this->client;
    }
}
