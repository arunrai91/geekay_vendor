<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter\Category;

use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyFulltextCollection;
use Amasty\Shopby\Model\Source\RenderCategoriesLevel;
use Amasty\Shopby\Plugin\Framework\Search\Request\Registry;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Search\Api\SearchInterface;

class FacetProvider
{
    public const CACHE_TAG = 'amshopby_category_counter';

    /**
     * 20 minutes
     */
    private const LIFETIME = 1200;

    /**
     * @var FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryManager
     */
    private $categoryManager;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Registry
     */
    private $requestRegistry;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        FilterRequestDataResolver $filterRequestDataResolver,
        CategoryRepositoryInterface $categoryRepository,
        CategoryManager $categoryManager,
        MessageManager $messageManager,
        SearchInterface $search,
        CacheInterface $cache,
        Registry $requestRegistry,
        SerializerInterface $serializer
    ) {
        $this->filterRequestDataResolver = $filterRequestDataResolver;
        $this->categoryRepository = $categoryRepository;
        $this->categoryManager = $categoryManager;
        $this->messageManager = $messageManager;
        $this->search = $search;
        $this->cache = $cache;
        $this->requestRegistry = $requestRegistry;
        $this->serializer = $serializer;
    }

    public function getFacetedData(Category $filter): array
    {
        if (!$this->isCachable($filter)) {
            return $this->resolveFacetedData($filter);
        }

        $cacheKey = $this->getCacheKey($filter);
        $result = $this->cache->load($cacheKey);
        if ($result !== false) {
            return $this->serializer->unserialize($result);
        }

        $optionsFacetedData = $this->resolveFacetedData($filter);

        if (!empty($optionsFacetedData)) {
            $this->cache->save(
                $this->serializer->serialize($optionsFacetedData),
                $cacheKey,
                [self::CACHE_TAG, CategoryModel::CACHE_TAG],
                self::LIFETIME
            );
        }

        return $optionsFacetedData;
    }

    public function isCachable(Category $filter): bool
    {
        return $filter->hasCurrentValue()
            && (count($filter->getLayer()->getState()->getFilters()) === 0)
            && $this->isRootTree($filter);
    }

    private function isRootTree(Category $filter): bool
    {
        $filterSetting = $filter->getSetting();

        return (int)$filterSetting->getRenderCategoriesLevel() === RenderCategoriesLevel::ROOT_CATEGORY
            || $filterSetting->getRenderAllCategoriesTree();
    }

    public function getCacheKey(Category $filter): string
    {
        return self::CACHE_TAG . '|s=' . $filter->getStoreId() . '|r=' . $this->categoryManager->getRootCategoryId();
    }

    private function resolveFacetedData(Category $filter): array
    {
        $optionsFacetedData = [];

        $productCollection = $filter->getLayer()->getProductCollection();
        if ($productCollection instanceof ShopbyFulltextCollection) {
            $result = $this->getSearchResult($filter);
            try {
                $optionsFacetedData = $productCollection->getFacetedData(
                    Category::FILTER_FIELD,
                    $result
                );
            } catch (StateException $e) {
                $this->catchBucketException($filter);
            }
        }

        return $optionsFacetedData;
    }

    /**
     * Request for count categories if enabled Render Full Category Tree.
     *
     * Request with same filters as main catalog request but for full category tree
     * and only for category bucket counters.
     *
     * @see \Amasty\Shopby\Plugin\Framework\Search\Request\Cleaner\CleanCategoryCounter
     */
    private function getSearchResult(Category $filter): ?SearchResultInterface
    {
        $searchResult = null;

        if ($filter->hasCurrentValue()
            && ($this->isRootTree($filter)
                || $this->isCurrentLevel($filter)
                || $this->isCurrentCategoryChildren($filter))
        ) {
            $categoryId = (int)$this->getCategoryIdByLevel($filter);

            $this->requestRegistry->setAdditionalCleaningAllowed(true);
            $searchCriteria = $filter->buildSearchCriteria($categoryId);
            // items are not needed, only counters of category aggregation
            $searchCriteria->setPageSize(0);
            $searchResult = $this->search->search($searchCriteria);
            $this->requestRegistry->setAdditionalCleaningAllowed(false);
        }

        return $searchResult;
    }

    private function isCurrentLevel(Category $filter): bool
    {
        return (int)$filter->getSetting()->getRenderCategoriesLevel()
            === RenderCategoriesLevel::CURRENT_CATEGORY_LEVEL;
    }

    private function isCurrentCategoryChildren(Category $filter): bool
    {
        return (int)$filter->getSetting()->getRenderCategoriesLevel()
            === RenderCategoriesLevel::CURRENT_CATEGORY_CHILDREN;
    }

    private function getCategoryIdByLevel(Category $filter): int
    {
        $parentCategory = $filter->getLayer()->getCurrentCategory()->getParentCategory();

        if (!$filter->isRenderAllTree()
            && (($filter->getRenderCategoriesLevel() === RenderCategoriesLevel::CURRENT_CATEGORY_LEVEL
                    && $filter->isMultiselect())
                || $filter->getCategoriesTreeDept() == Category::MIN_CATEGORY_DEPTH)
            && $parentCategory->getIsAnchor()
        ) {
            $categoryId = $parentCategory->getId();
        } else {
            $categoryId = $this->categoryManager->getRootCategoryId();
        }

        return (int)$categoryId;
    }

    private function catchBucketException(Category $filter): void
    {
        $currentValue = $this->filterRequestDataResolver->getCurrentValue($filter);
        if (is_array($currentValue)) {
            $categoryId = current($currentValue);
            try {
                $category = $this->categoryRepository->get(
                    $categoryId,
                    $this->categoryManager->getCurrentStoreId()
                );
            } catch (NoSuchEntityException $e) {
                $category = $this->getRootCategory();
            }
        } else {
            $category = $this->getRootCategory();
        }

        $this->messageManager->addErrorMessage(
            __(
                'Make sure that "%1"(id:%2) category for current store is anchored',
                $category->getName(),
                $category->getId()
            )
        );
    }

    private function getRootCategory(): CategoryModel
    {
        return $this->categoryRepository->get(
            $this->categoryManager->getRootCategoryId(),
            $this->categoryManager->getCurrentStoreId()
        );
    }
}
