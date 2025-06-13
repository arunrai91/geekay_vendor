<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Rule\Filters;

use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\InventoryResolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Select;

class ExcludeFilter implements FilterInterface
{
    /**
     * @var InventoryResolver
     */
    private InventoryResolver $inventoryResolver;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var Product
     */
    private Product $productResource;

    public function __construct(
        InventoryResolver $inventoryResolver,
        CollectionFactory $productCollectionFactory,
        Product $productResource
    ) {
        $this->inventoryResolver = $inventoryResolver;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productResource = $productResource;
    }

    public function apply(ProductCollection $productCollection, Feed $model): void
    {
        $excludedIds = [];
        if ($model->getExcludeDisabled()) {
            $productCollection->addAttributeToFilter(
                'status',
                ['eq' => Status::STATUS_ENABLED]
            );
            if ($model->getExcludeSubDisabled()) {
                $excludedIds = $this->getSubDisabledIds((int)$model->getStoreId());
            }
        }

        if ($model->getExcludeNotVisible()) {
            $productCollection->addAttributeToFilter(
                'visibility',
                ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]
            );
        }

        if ($model->getExcludeOutOfStock()) {
            $outOfStockProductIds = $this->inventoryResolver->getOutOfStockProductIds();
            $excludedIds = array_unique(array_merge($excludedIds, $outOfStockProductIds));
        }

        if (!empty($excludedIds)) {
            $productCollection->addFieldToFilter(
                'entity_id',
                ['nin' => $excludedIds]
            );
        }
    }

    private function getSubDisabledIds(int $storeId): array
    {
        $disabledParentProductsSelect = $this->getDisabledParentProductsSelect($storeId);

        $subDisabledProductsCollection = $this->productCollectionFactory->create();
        $subDisabledProductsCollection->getSelect()->join(
            ['rel' => $this->productResource->getTable('catalog_product_relation')],
            'e.entity_id = rel.child_id',
            []
        )->where('rel.parent_id IN (?)', $disabledParentProductsSelect);

        return $subDisabledProductsCollection->getAllIds();
    }

    private function getDisabledParentProductsSelect(int $storeId): Select
    {
        $disabledParentsCollection = $this->productCollectionFactory->create();
        $linkField = $disabledParentsCollection->getProductEntityMetadata()->getLinkField();

        $disabledParentsCollection->addStoreFilter($storeId);
        $disabledParentsCollection->addAttributeToFilter(
            'status',
            ['eq' => Status::STATUS_DISABLED]
        );

        return $disabledParentsCollection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(['e.' . $linkField])
            ->join(
                ['rel' => $this->productResource->getTable('catalog_product_relation')],
                'rel.parent_id = e.' . $linkField,
                []
            )->distinct();
    }
}
