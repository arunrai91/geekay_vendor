<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class InvalidateCatalogSearchIndex extends Value
{
    /**
     * @var IndexerRegistry
     */
    private IndexerRegistry $indexerRegistry;

    public function __construct(
        IndexerRegistry $indexerRegistry,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @return InvalidateCatalogSearchIndex
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $indexer = $this->indexerRegistry->get('catalogsearch_fulltext');
            $indexer->invalidate();
        }

        return parent::afterSave();
    }
}
