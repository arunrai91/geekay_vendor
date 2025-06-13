<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Indexer\Product;

use Amasty\Feed\Model\Indexer\AbstractIndexer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class ProductFeedIndexer extends AbstractIndexer
{
    /**
     * Override constructor. Indexer is changed
     */
    //phpcs:ignore
    public function __construct(
        IndexBuilder $productIndexBuilder,
        ManagerInterface $eventManager
    ) {
        parent::__construct($productIndexBuilder, $eventManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($productIds)
    {
        try {
            $this->indexBuilder->reindexByProductIds(array_unique($productIds));
            $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $productIds);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($productId)
    {
        try {
            $this->indexBuilder->reindexByProductId($productId);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    public function executeFull()
    {
        try {
            $this->indexBuilder->reindexFull();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
