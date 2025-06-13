<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class OnSaleProvider
{
    /**
     * @var array array($storeId => [$groupId => int[]])
     */
    private $onSaleProductIds;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    public function __construct(CollectionFactory $productCollectionFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function isProductOnSale(int $entityId, int $storeId, int $groupId): bool
    {
        $onSaleProducts = $this->getOnSaleProductIds($storeId, $groupId);

        return \in_array($entityId, $onSaleProducts, true);
    }

    /**
     * @return int[]
     */
    public function getOnSaleProductIds(int $storeId, int $groupId): array
    {
        if (!isset($this->onSaleProductIds[$storeId][$groupId])) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addStoreFilter($storeId);
            $collection->addPriceData($groupId);

            $select = $collection->getSelect();
            $select->where('price_index.final_price < price_index.price');
            $select->group('e.entity_id');
            $select->reset(\Magento\Framework\DB\Select::COLUMNS);
            $select->columns(['id' => 'e.entity_id']);

            $result = $collection->getConnection()->fetchCol($select);

            $this->onSaleProductIds[$storeId][$groupId] = !empty($result) ? array_map('intval', $result) : [];
        }
        
        return $this->onSaleProductIds[$storeId][$groupId];
    }
}
