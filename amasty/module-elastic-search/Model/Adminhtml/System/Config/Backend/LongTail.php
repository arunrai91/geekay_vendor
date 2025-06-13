<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Adminhtml\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class LongTail extends \Magento\Framework\App\Config\Value
{
    /**
     * @var IndexerRegistry
     */
    protected IndexerRegistry $indexerRegistry;

    public function __construct(
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        IndexerRegistry      $indexerRegistry,
        ?AbstractResource    $resource = null,
        ?AbstractDb          $resourceCollection = null,
        array                $data = []
    ) {
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save call
     * Invalidate catalog search index if longtail was changed
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();

        if ($this->isValueChanged()) {
            $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->invalidate();
        }
        return $this;
    }
}
