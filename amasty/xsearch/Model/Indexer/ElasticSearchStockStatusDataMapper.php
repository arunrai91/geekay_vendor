<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Indexer;

use Amasty\Xsearch\Model\Indexer\ElasticSearchStockStatusStructureMapper;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ElasticSearchStockStatusDataMapper
{
    public const STOCK_IN_STOCK = 1;

    public const STOCK_OUT_OF_STOCK = 2;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $inStockProductIds = [];

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockStatusResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockStatusResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockStatusResource = $stockStatusResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $stockStatusDocumentData = [];
        $fieldName = ElasticSearchStockStatusStructureMapper::STOCK_STATUS;
        foreach ($documentData as $productId => $document) {
            if (!isset($document[$fieldName]) && !isset($context['document'][$productId][$fieldName])) {
                $stockStatus = $this->isProductInStock($productId, (int)$storeId);
                $stockStatusDocumentData[$productId][$fieldName] = $stockStatus;
            }
        }

        return $stockStatusDocumentData;
    }

    private function isProductInStock(int $entityId, int $storeId): int
    {
        if (in_array($entityId, $this->getInStockProductIds($storeId), true)) {
            return self::STOCK_IN_STOCK;
        }

        return self::STOCK_OUT_OF_STOCK;
    }

    /**
     * @return int[]
     */
    private function getInStockProductIds($storeId): array
    {
        if (!isset($this->inStockProductIds[$storeId])) {
            $collection = $this->getCollectionWithStock($storeId);
            $this->inStockProductIds[$storeId] = array_map('intval', $collection->getAllIds());
        }

        return $this->inStockProductIds[$storeId];
    }

    /**
     * Get Product Collection with Joined Stock.
     * Resolver compatibility with MSI by store emulation.
     *
     * Full emulation may lead to error "Required parameter 'theme_dir' was not passed".
     */
    private function getCollectionWithStock(int $storeId): Collection
    {
        /** @var Collection $collection */
        $collection = $this->productCollectionFactory->create()->addStoreFilter($storeId);

        $currentStore = $this->storeManager->getStore();
        // Emulate store for MSI plugin which gets stock by current store
        $this->storeManager->setCurrentStore($storeId);
        $this->stockStatusResource->addStockDataToCollection($collection, true);
        $this->storeManager->setCurrentStore($currentStore);

        return $collection;
    }
}
