<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer;

use Amasty\ElasticSearch\Model\Adapter\AdapterInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;

class DataHandler implements IndexerInterface
{
    /**
     * Default batch size
     */
    public const BATCH_SIZE = 1000;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $batchSize;

    public function __construct(
        IndexStructureInterface $indexStructure,
        ScopeResolverInterface $scopeResolver,
        AdapterInterface $adapter,
        Batch $batch,
        array $data = [],
        $batchSize = self::BATCH_SIZE
    ) {
        $this->indexStructure = $indexStructure;
        $this->scopeResolver = $scopeResolver;
        $this->adapter = $adapter;
        $this->batch = $batch;
        $this->data = $data;
        $this->batchSize = $batchSize;
    }

    /**
     * Add entities data to index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $scopeId = $this->resolveScopeId($dimensions);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documents) {
            $documents = $this->adapter->prepareDocuments($documents, $scopeId, $this->getIndexerId());
            $this->adapter->saveDocuments($documents, $scopeId, $this->getIndexerId());
        }

        $this->adapter->updateAlias($scopeId, $this->getIndexerId());

        return $this;
    }

    /**
     * Remove entities data from index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $scopeId = $this->resolveScopeId($dimensions);
        $documentIds = [];
        foreach ($documents as $document) {
            $documentIds[$document] = $document;
        }
        $this->adapter->deleteDocuments($documentIds, $scopeId, $this->getIndexerId());
        return $this;
    }

    /**
     * Remove all data from index
     *
     * @param Dimension[] $dimensions
     * @return IndexerInterface
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexerId(), $dimensions);
        $this->indexStructure->create($this->getIndexerId(), [], $dimensions);

        return $this;
    }

    /**
     * Define if engine is available
     *
     * @param Dimension[] $dimensions
     * @return bool
     */
    public function isAvailable($dimensions = [])
    {
        return $this->adapter->ping();
    }

    private function getIndexerId(): string
    {
        return (string)$this->data['indexer_id'];
    }

    /**
     * @param Dimension[] $dimensions
     */
    private function resolveScopeId(array $dimensions): int
    {
        $dimension = current($dimensions);
        return (int)$this->scopeResolver->getScope($dimension->getValue())->getId();
    }
}
