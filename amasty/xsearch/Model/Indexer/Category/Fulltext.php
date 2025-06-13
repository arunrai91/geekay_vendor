<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Indexer\Category;

use Amasty\Xsearch\Block\Search\Category;
use Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action\Full;
use Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action\FullFactory;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Fulltext implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    public const INDEXER_ID = 'amasty_xsearch_category_fulltext';

    /**
     * @var Full
     */
    private $fullAction;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;

    /**
     * @var SearchRequestConfig
     */
    private $searchRequestConfig;

    /**
     * @var array
     */
    private $data;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cacheManager;

    public function __construct(
        FullFactory $fullActionFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        SearchRequestConfig $searchRequestConfig,
        \Magento\Framework\App\CacheInterface $cacheManager,
        array $data
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
        $this->cacheManager = $cacheManager;
    }

    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);

            $saveHandler->cleanIndex([$dimension]);
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId));

        }

        $this->searchRequestConfig->reset();
        $this->cleanCache();
    }

    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->deleteIndex([$dimension], new \ArrayIterator($ids));
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
        }

        $this->cleanCache();
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    private function cleanCache(): void
    {
        $this->cacheManager->clean([Category::DEFAULT_CACHE_TAG . '_' . Category::CATEGORY_BLOCK_TYPE]);
    }
}
