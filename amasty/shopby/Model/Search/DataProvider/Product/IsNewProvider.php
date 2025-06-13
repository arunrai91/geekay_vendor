<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search\DataProvider\Product;

use Amasty\Shopby\Model\Layer\Filter\IsNew\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class IsNewProvider
{
    /**
     * @var array [$storeId => int[]]
     */
    private $newProductIds = [];

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Helper
     */
    private $isNewHelper;

    public function __construct(CollectionFactory $productCollectionFactory, Helper $isNewHelper)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->isNewHelper = $isNewHelper;
    }

    public function isProductNew(int $entityId, int $storeId): bool
    {
        $newProductIds = $this->getNewProductIds($storeId);

        return isset($newProductIds[$entityId]);
    }

    /**
     * @return int[]
     */
    public function getNewProductIds(int $storeId): array
    {
        if (!isset($this->newProductIds[$storeId])) {
            $this->newProductIds[$storeId] = [];

            /** @var Collection $collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addStoreFilter($storeId);
            $this->isNewHelper->addNewFilter($collection);

            $ids = array_map('intval', $collection->getAllIds());
            $this->newProductIds[$storeId] = array_combine($ids, $ids);
        }

        return $this->newProductIds[$storeId];
    }
}
