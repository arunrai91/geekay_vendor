<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Category\Repository;
use Amasty\Feed\Model\Category\ResourceModel\Collection;
use Amasty\Feed\Model\Category\ResourceModel\CollectionFactory;
use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class Category implements RowCustomizerInterface
{
    /**
     * @var Product
     */
    private $export;

    /**
     * @var string[]
     */
    private $mappingCategories;

    /**
     * @var array [
     * 'categoryCode1' => [categoryId => categoryName],
     * 'categoryCode2' => [categoryId => categoryName]
     * ]
     */
    private $mappingData = [];

    /**
     * @var array
     */
    private $rowCategories;

    /**
     * @var array
     */
    private $categoriesPath;

    /**
     * @var array
     */
    private $categoriesLast;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Repository
     */
    private $categoryRepository;

    public function __construct(
        Product $export,
        CollectionFactory $categoryCollectionFactory,
        Repository $categoryRepository
    ) {
        $this->export = $export;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
    }

    public function prepareData($collection, $productIds)
    {
        $attributeStorage = $this->export->getAttributesStorage();
        if ($attributeStorage->hasAttributes(FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE)
            || $attributeStorage->hasAttributes(FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE)
        ) {
            $this->mappingCategories = array_merge(
                $attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE),
                $attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE)
            );

            $categoryCollection = $this->categoryCollectionFactory->create()
                ->addOrder('name')
                ->addFieldToFilter('code', ['in' => $this->mappingCategories]);
            $this->mapCategories($categoryCollection);
            $multiRowData = $this->export->getMultirawData();
            $this->rowCategories = $this->getCompatibleCategories($multiRowData[Product::ROW_CATEGORIES_KEY]);
            $this->categoriesPath = $this->export->getCategoriesPath();
            $this->categoriesLast = $this->export->getCategoriesLast();
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];
        $customData[FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE] = [];
        $customData[FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE] = [];

        if (is_array($this->mappingCategories)) {
            foreach ($this->mappingCategories as $code) {
                if (isset($this->rowCategories[$code][$productId])) {
                    $categories = (array)$this->rowCategories[$code][$productId];
                    $lastCategoryId = $this->getLastCategoryId($categories);
                    if (isset($this->categoriesLast[$lastCategoryId]) && is_array($this->mappingCategories)) {
                        $lastCategoryVar = $this->categoriesLast[$lastCategoryId];
                        $customData[FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE][$code] =
                            $this->mappingData[$code][$lastCategoryId]
                            ?? $lastCategoryVar;
                    }
                    $customData[FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE][$code] = implode(
                        ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                        $this->getCategoriesPath($categories, (string)$code)
                    );
                }
            }
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

    private function getLastCategoryId(array $categories): ?int
    {
        while (count($categories) > 0) {
            $endCategoryId = array_pop($categories);
            foreach ($this->mappingCategories as $code) {
                if (isset($this->mappingData[$code][$endCategoryId])) {
                    return $endCategoryId;
                }
            }
        }

        return null;
    }

    private function getCategoriesPath(array $categories, string $code): array
    {
        $categoriesPath = [];
        foreach ($categories as $categoryId) {
            if (isset($this->categoriesPath[$categoryId])) {
                $path = $this->categoriesPath[$categoryId];
                $mappingPath = [];
                foreach ($path as $id => $var) {
                    if (isset($this->mappingData[$code][$id])) {
                        $mappingPath[$id] = $this->mappingData[$code][$id];
                    }
                }
                $categoriesPath[] = implode('/', $mappingPath);
            }
        }

        return $categoriesPath;
    }

    private function mapCategories(Collection $categoryCollection): void
    {
        foreach ($this->categoryRepository->getItemsWithDeps($categoryCollection) as $category) {
            foreach ($category->getMapping() as $mapping) {
                // Skipped categories could not have name. So we do not process them.
                if (null === $mapping->getVariable() || $mapping->getDataByKey('skip')) {
                    continue;
                }
                $this->mappingData[$category->getCode()][$mapping->getCategoryId()] = $mapping->getVariable();
            }
        }
    }

    /**
     * @param array $rowsCategories ['productId' => [0 => categoryId, 1 => categoryId]]
     *
     * @return array ['mapCategory' => [
     * 'productId' => [0 => categoryId, 1 => categoryId]
     * ]]
     */
    private function getCompatibleCategories(array $rowsCategories): array
    {
        $categoriesMap = [];
        foreach ($rowsCategories as $productId => $categories) {
            foreach ($this->mappingData as $catCode => $mappingDatum) {
                $rowCategoriesMap = array_flip(array_intersect_key(array_flip($categories), $mappingDatum));
                $categoriesMap[$catCode][$productId] = $rowCategoriesMap;
            }
        }

        return $categoriesMap;
    }
}
