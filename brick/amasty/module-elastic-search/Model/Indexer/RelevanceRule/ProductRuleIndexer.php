<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\RelevanceRule;

class ProductRuleIndexer extends AbstractIndexer
{
    /**
     * @inheritdoc
     */
    protected function doExecuteRow($id)
    {
        $this->getIndexBuilder()->reindexByIds([$id]);
    }

    /**
     * @inheritdoc
     */
    protected function doExecuteList($ids)
    {
        $this->getIndexBuilder()->reindexByIds(array_unique($ids));
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }
}
