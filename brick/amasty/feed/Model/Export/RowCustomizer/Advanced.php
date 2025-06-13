<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as Export;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Amasty\Feed\Model\ResourceModel\ProductCategoriesProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Advanced implements RowCustomizerInterface
{
    /**
     * @var string[]
     */
    private $attributes = [];

    /**
     * Storage for product categories [product_id => category_ids]
     * @var string[]
     */
    private $productCategoryMapping = [];

    /**
     * @var Export
     */
    private $export;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCategoriesProvider
     */
    private $productCategoriesProvider;

    public function __construct(
        Export $export,
        ProductRepositoryInterface $productRepository,
        ProductCategoriesProvider $productCategoriesProvider
    ) {
        $this->export = $export;
        $this->productRepository = $productRepository;
        $this->productCategoriesProvider = $productCategoriesProvider;
    }

    public function prepareData($collection, $productIds)
    {
        $attributesStorage = $this->export->getAttributesStorage();
        if ($attributesStorage->hasAttributes(FeedAttributesStorage::PREFIX_ADVANCED_ATTRIBUTE)) {
            $this->attributes = $attributesStorage->getAttributesByType(
                FeedAttributesStorage::PREFIX_ADVANCED_ATTRIBUTE
            );
            $productCategories = $this->productCategoriesProvider->getCategoryIds($productIds);
            $productCategories += array_fill_keys($productIds, null);
            ksort($productCategories);

            $this->productCategoryMapping = $productCategories;
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $dataRow[Composite::CUSTOM_DATA_KEY][FeedAttributesStorage::PREFIX_ADVANCED_ATTRIBUTE] = [];
        $advancedAttribute = &$dataRow[Composite::CUSTOM_DATA_KEY][FeedAttributesStorage::PREFIX_ADVANCED_ATTRIBUTE];
        foreach ($this->attributes as $attribute) {
            $result = '';
            if ($attribute === 'category_ids') {
                $result = $this->getCategoryIds((int)$productId);
            }
            $advancedAttribute[$attribute] = $result;
        }

        return $dataRow;
    }

    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    private function getCategoryIds(int $productId): ?string
    {
        if (!empty($this->productCategoryMapping[$productId])) {
            return $this->productCategoryMapping[$productId];
        }

        $product = $this->productRepository->getById($productId);
        $categoryIds = $product->getCategoryIds();

        return implode(",", $categoryIds);
    }
}
