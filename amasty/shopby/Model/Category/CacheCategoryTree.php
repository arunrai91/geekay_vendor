<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Category;

use Amasty\Base\Model\Serializer;
use Amasty\Shopby\Model\Category\CategoryDataInterfaceFactory;
use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\Shopby\Model\Layer\Filter\Category\FacetProvider;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;

class CacheCategoryTree
{
    public const CACHE_TAG = 'amshopby_category_tree_';

    private const LIFETIME = 86400;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ExtendedCategoryCollection
     */
    private $extendedCategoryCollection;

    /**
     * @var CategoryDataInterfaceFactory
     */
    private $categoryDataFactory;

    /**
     * @var CategoryTreeFactory
     */
    private $treeFactory;

    /**
     * @var FacetProvider
     */
    private $facetProvider;

    /**
     * @var CategoryTree|null
     */
    private ?CategoryTree $currentCategoryTree = null;

    public function __construct(
        CacheInterface $cache,
        Serializer $serializer,
        ExtendedCategoryCollection $extendedCategoryCollection,
        CategoryDataInterfaceFactory $categoryDataFactory,
        CategoryTreeFactory $treeFactory,
        FacetProvider $facetProvider
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->extendedCategoryCollection = $extendedCategoryCollection;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->treeFactory = $treeFactory;
        $this->facetProvider = $facetProvider;
    }

    /**
     * @throws LocalizedException
     */
    public function get(Category $filter): CategoryTree
    {
        if ($this->currentCategoryTree !== null) {
            return $this->currentCategoryTree;
        }

        $cacheKey = $this->getCacheKey($filter);
        $result = $this->cache->load($cacheKey);
        if ($result) {
            $this->currentCategoryTree = $this->getCategoryTreeObject($this->serializer->unserialize($result), $filter);
            return $this->currentCategoryTree;
        }
        $treeData = $this->extendedCategoryCollection->getCategoryTreeData($filter);
        $this->cache->save(
            $this->serializer->serialize($treeData),
            $cacheKey,
            [self::CACHE_TAG, CategoryModel::CACHE_TAG],
            self::LIFETIME
        );

        $this->currentCategoryTree = $this->getCategoryTreeObject($treeData, $filter);

        return $this->currentCategoryTree;
    }

    /**
     * @throws LocalizedException
     */
    public function getCategoryTreeObject(array $treeData, Category $filter): CategoryTree
    {
        if (!isset($treeData['count'], $treeData['categories'], $treeData['startPath'])) {
            throw new LocalizedException(__('Incorrect data for object: %1', CategoryTree::class));
        }

        $categoryTree = $this->treeFactory->create();

        try {
            $optionsFacetedData = $this->facetProvider->getFacetedData($filter);
        } catch (StateException $e) {
            $categoryTree->setCount(0);
            $categoryTree->setCategories([]);
            $categoryTree->setStartPath('');
            return $categoryTree;
        }

        $categories = [];
        foreach ($treeData['categories'] as $categoryData) {
            $categoryId = $categoryData[CategoryDataInterface::ID];
            if (!isset($optionsFacetedData[$categoryId])) {
                continue;
            }
            $categoryData[CategoryDataInterface::COUNT] = $optionsFacetedData[$categoryId]['count'];
            $categories[$categoryId] = $this->categoryDataFactory->create(['data' => $categoryData]);
        }
        $categoryTree->setCount(count($categories));
        $categoryTree->setStartPath($treeData['startPath']);
        $categoryTree->setCategories($categories);

        return $categoryTree;
    }

    public function getCacheKey(Category $filter): string
    {
        return self::CACHE_TAG . '|s=' . $filter->getStoreId() . '|cat='
            . $this->extendedCategoryCollection->getStartCategory($filter)->getId();
    }

    public function getCurrentCategoryTree(): ?CategoryTree
    {
        return $this->currentCategoryTree;
    }
}
