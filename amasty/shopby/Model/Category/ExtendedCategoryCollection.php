<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category;

use Amasty\Base\Model\Serializer;
use Amasty\Shopby\Helper\Category as CategoryHelper;
use Amasty\Shopby\Model\Category\CategoryDataInterfaceFactory;
use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Flat\Collection as CategoryFlatCollection;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;

class ExtendedCategoryCollection
{
    public const CACHE_TAG = 'amshopby_excluded_category_ids_';

    private const LIFETIME = 1800;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryManager
     */
    private $categoryManager;

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        CategoryManager $categoryManager,
        CategoryHelper $categoryHelper,
        ProductMetadataInterface $productMetadata,
        Escaper $escaper,
        ResourceConnection $resource,
        CacheInterface $cache,
        Serializer $serializer
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryManager = $categoryManager;
        $this->categoryHelper = $categoryHelper;
        $this->productMetadata = $productMetadata;
        $this->escaper = $escaper;
        $this->resource = $resource;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * @return CategoryDataInterface[]
     * @throws LocalizedException
     */
    public function getCategoryTreeData(Category $filter): array
    {
        $categories = [];
        $startCategory = $this->getStartCategory($filter);
        $startPath = $startCategory->getPath();

        $collection = $this->getExtendedCategoryCollection($filter);
        $currentCategoryParents = $filter->getLayer()->getCurrentCategory()->getParentIds();
        foreach ($collection as $category) {
            $isAllowed = $this->isAllowedOnEnterprise($category);
            if (!$isAllowed
                || (
                    !$filter->isRenderAllTree()
                    && !in_array($category->getParentId(), $currentCategoryParents)
                    && $filter->getCategoriesTreeDept() != Category::MIN_CATEGORY_DEPTH
                    && strpos($category->getPath(), $startPath) !== 0
                )
            ) {
                continue;
            }

            $categoryData = [
                CategoryDataInterface::PATH => $category->getPath(),
                CategoryDataInterface::PARENT_PATH => $category->getParentPath(),
                CategoryDataInterface::LABEL => $this->escaper->escapeHtml($category->getName()),
                CategoryDataInterface::ID => (int)$category->getId(),
                CategoryDataInterface::PERMISSIONS => $category->getPermissions() ?? []
            ];
            $categories[] = $categoryData;
        }
        $categoryTreeData = [];
        $categoryTreeData['count'] = count($categories);
        $categoryTreeData['startPath'] = $startPath;
        $categoryTreeData['categories'] = $categories;

        return $categoryTreeData;
    }

    /**
     * @param Category $filter
     *
     * @return CategoryCollection|CategoryFlatCollection
     * @throws LocalizedException
     * @throws Exception
     */
    private function getExtendedCategoryCollection(Category $filter)
    {
        $startCategory = $this->getStartCategory($filter);
        $excludedCategoryIds = $this->getExcludedCategoryIds($filter);
        if ($excludedCategoryIds && in_array($startCategory->getId(), $excludedCategoryIds)) {
            /** @var CategoryCollection $emptyCollection */
            $emptyCollection = $startCategory->getCollection();
            $emptyCollection->getSelect()->where('null');

            return $emptyCollection;
        }

        $minLevel = $startCategory->getLevel();
        $maxLevel = $minLevel + $filter->getCategoriesTreeDept();

        /** @var CategoryCollection|CategoryFlatCollection $collection */
        $collection = $startCategory->getCollection();
        $isFlat = $collection instanceof CategoryFlatCollection;
        $mainTablePrefix = $isFlat ? 'main_table.' : '';
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter($mainTablePrefix . 'is_active', 1)
            ->addFieldToFilter($mainTablePrefix . 'path', ['like' => $startCategory->getPath() . '%'])
            ->addFieldToFilter($mainTablePrefix . 'level', ['gt' => $minLevel])
            ->setOrder(
                $mainTablePrefix . 'position',
                Select::SQL_ASC
            );

        if (!empty($excludedCategoryIds)) {
            $idField = $isFlat ? 'entity_id' : $collection->getEntity()->getIdFieldName();
            $collection->addFieldToFilter($mainTablePrefix . $idField, ['nin' => $excludedCategoryIds]);
        }
        if (!$filter->isRenderAllTree()) {
            $collection->addFieldToFilter($mainTablePrefix . 'level', ['lteq' => $maxLevel]);
        }

        $mainTablePrefix = $isFlat ? 'main_table.' : 'e.';
        $collection->getSelect()->joinLeft(
            ['parent' => $collection->getMainTable()],
            $mainTablePrefix . 'parent_id = parent.entity_id',
            ['parent_path' => 'parent.path']
        );

        return $collection;
    }

    public function getExcludedCategoryIds(Category $filter): array
    {
        try {
            $rootPath = $this->categoryRepository->get($this->categoryManager->getRootCategoryId())->getPath();
            $cacheKey = $this->getCacheKey($filter, $rootPath);
            $result = $this->cache->load($cacheKey);
            if ($result) {
                return $this->serializer->unserialize($result);
            }
            $collection = $this->categoryCollectionFactory->create();
            $collection->addFieldToFilter('path', ['like' => $rootPath . '/%'])
                ->setStore($filter->getStoreId())
                ->addAttributeToFilter(Category::EXCLUDE_CATEGORY_FROM_FILTER, Category::TRUE, 'left');
            $idField = $collection->getEntity()->getIdFieldName();
            $collection->getSelect()->reset(Select::COLUMNS)->columns([$idField]);
            $categoryIds = $this->resource->getConnection()->fetchCol($collection->getSelect());
            $this->cache->save(
                $this->serializer->serialize($categoryIds),
                $cacheKey,
                [self::CACHE_TAG, CategoryModel::CACHE_TAG],
                self::LIFETIME
            );

            return $categoryIds;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function getCacheKey(Category $filter, string $rootPath): string
    {
        return self::CACHE_TAG . '|s=' . $filter->getStoreId() . '|path=' . $rootPath;
    }

    /**
     * Retrieve start category for bucket prepare
     *
     * @param Category $filter
     *
     * @return CategoryModel
     */
    public function getStartCategory(Category $filter): CategoryModel
    {
        if ($filter->getCategoriesTreeDept() == Category::MIN_CATEGORY_DEPTH
            && !$filter->getLayer()->getCurrentCategory()->getChildrenCount()
            && !$filter->isRenderAllTree()
        ) {
            return $filter->getLayer()->getCurrentCategory()->getParentCategory();
        }

        return $this->categoryHelper->getStartCategory();
    }

    private function isAllowedOnEnterprise(CategoryModel $category): bool
    {
        $isAllowed = true;
        if ($this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME) {
            $permissions = $category->getPermissions();
            if (isset($permissions['grant_catalog_category_view'])) {
                $isAllowed = $permissions['grant_catalog_category_view'] !== Category::DENY_PERMISSION;
            }
        }

        return $isAllowed;
    }
}
