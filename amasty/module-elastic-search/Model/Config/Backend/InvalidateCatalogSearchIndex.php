<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Indexer\IndexerRegistry;

class InvalidateCatalogSearchIndex extends Value
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
