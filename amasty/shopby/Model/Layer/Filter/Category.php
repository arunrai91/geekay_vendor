<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Helper\Category as CategoryHelper;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\Shopby\Model\Category\CacheCategoryTree;
use Amasty\Shopby\Model\Category\CategoryTree;
use Amasty\Shopby\Model\Category\ExtendedCategoryCollection;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Layer\Filter\Category\FacetProvider;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyFulltextCollection;
use Amasty\Shopby\Model\Source\CategoryTreeDisplayMode;
use Amasty\Shopby\Model\Source\SortOptionsBy;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Amasty\ShopbyBase\Model\CustomFilterInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory as CategoryDataProviderFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Phrase;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @method CategoryItems getItems()
 */
class Category extends AbstractFilter implements CustomFilterInterface
{
    public const MIN_CATEGORY_DEPTH = 1;

    public const DENY_PERMISSION = '-2';

    public const FILTER_FIELD = 'category';

    public const EXCLUDE_CATEGORY_FROM_FILTER = 'am_exclude_from_filter';

    public const TRUE = 1;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * @var CategoryItemsFactory
     */
    private $categoryItemsFactory;

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var FilterSettingResolver
     */
    private $filterSettingResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Category\FacetProvider
     */
    private $facetProvider;

    /**
     * @var CacheCategoryTree
     */
    private $cacheCategoryTree;

    /**
     * @var ExtendedCategoryCollection
     */
    private $extendedCategoryCollection;

    /**
     * @var CategoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Escaper $escaper,
        CategoryDataProviderFactory $categoryDataProviderFactory,
        CategoryItemsFactory $categoryItemsFactory,
        CategoryHelper $categoryHelper,
        FilterRequestDataResolver $filterRequestDataResolver,
        FilterSettingResolver $filterSettingResolver,
        ConfigProvider $configProvider,
        FacetProvider $facetProvider,
        CacheCategoryTree $cacheCategoryTree,
        ExtendedCategoryCollection $extendedCategoryCollection,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->categoryItemsFactory = $categoryItemsFactory;
        $this->categoryHelper = $categoryHelper;
        $this->filterRequestDataResolver = $filterRequestDataResolver;
        $this->filterSettingResolver = $filterSettingResolver;
        $this->configProvider = $configProvider;
        $this->facetProvider = $facetProvider;
        $this->cacheCategoryTree = $cacheCategoryTree;
        $this->extendedCategoryCollection = $extendedCategoryCollection;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Apply category filter to product collection
     *
     * @param   RequestInterface $request
     * @return  $this
     */
    public function apply(RequestInterface $request)
    {
        if ($this->filterRequestDataResolver->isApplied($this)) {
            return $this;
        }

        $categoryValueFromParam = $this->filterRequestDataResolver->getFilterParam($this);
        $categoryId = $categoryValueFromParam ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }

        $categoryIds = explode(',', $categoryId);
        $categoryIds = array_unique($categoryIds);
        $category = $this->dataProvider->getCategory();
        if ($this->isMultiselect() && $categoryValueFromParam) {
            $categoryIds = $this->excludeCategoriesFromFilter($categoryIds);
            if (empty($categoryIds)) {
                return $this;
            }

            $this->filterRequestDataResolver->setCurrentValue($this, $categoryIds);
            $child = $this->categoryCollectionFactory->create();
            $child->addFieldToFilter($category->getIdFieldName(), ['in' => $categoryIds]);
            $child->addAttributeToSelect('name');
            $categoriesInState = [];
            foreach ($categoryIds as $categoryId) {
                if ($currentCategory = $child->getItemById($categoryId)) {
                    $categoriesInState[$currentCategory->getId()] = $currentCategory->getName();
                }
            }
            foreach ($categoriesInState as $key => $category) {
                $state = $this->_createItem($category, $key);
                $this->getLayer()->getState()->addFilter($state);
            }
        } else {
            $this->filterRequestDataResolver->setCurrentValue($this, $categoryIds);
            $this->dataProvider->setCategoryId($categoryId);
            if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
                $this->getLayer()->getState()->addFilter(
                    $this->_createItem(
                        $this->dataProvider->getCategory()->getName(),
                        $categoryId
                    )
                );
            }
        }
        /** @var ShopbyFulltextCollection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $productCollection->addFieldToFilter(CategoryHelper::ATTRIBUTE_CODE, $categoryIds);

        return $this;
    }

    private function excludeCategoriesFromFilter(array $categoryIds): array
    {
        $excludedIds = $this->extendedCategoryCollection->getExcludedCategoryIds($this);

        return array_values(array_diff($categoryIds, $excludedIds));
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Get filter name
     *
     * @return Phrase
     */
    public function getName()
    {
        return __('Category');
    }

    /**
     * Get fiter items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        if (!$this->categoryHelper->isCategoryFilterExtended()) {
            return count($this->getItems()->getItems(null));
        }

        return $this->getItems()->getCount();
    }

    /**
     * @return $this|AbstractFilter
     * @throws LocalizedException
     */
    protected function _initItems()
    {
        /** @var CategoryItems $itemsCollection */
        $itemsCollection = $this->categoryItemsFactory->create();
        $categoryTree = $this->cacheCategoryTree->get($this);
        if ($categoryTree && $this->isPopulateCategoryCollection($categoryTree)) {
            $itemsCollection->setStartPath($categoryTree->getStartPath());
            $itemsCollection->setCount($categoryTree->getCount());
            foreach ($categoryTree->getCategories() as $categoryData) {
                $itemsCollection->addItem(
                    $categoryData->getParentPath(),
                    $this->_createItem($categoryData->getLabel(), $categoryData->getId(), $categoryData->getCount())
                );
            }
        }

        switch ($this->getSetting()->getSortOptionsBy()) {
            case SortOptionsBy::NAME:
                $itemsCollection->sortOptions();
                break;
            case SortOptionsBy::PRODUCT_COUNT:
                $itemsCollection->sortOptionsByCount();
                break;
        }
        $this->_items = $itemsCollection;

        return $this;
    }

    private function isPopulateCategoryCollection(CategoryTree $categoryTree): bool
    {
        return !$this->configProvider->isHideFilterWithOneOption()
            || $categoryTree->getCount() > 1;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $optionsFacetedData = $this->getFacetedData();
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();

        if ($categories instanceof CategoryCollection) {
            $categories->addAttributeToSelect('thumbnail');
        }

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if ($category->getIsActive()
                    && $category->getIsAnchor()
                    && isset($optionsFacetedData[$category->getId()])
                ) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $optionsFacetedData[$category->getId()]['count']
                    );
                }
            }
        }

        $itemsData = $this->itemDataBuilder->build();
        if ($this->configProvider->isHideFilterWithOneOption()
            && count($itemsData) == 1
            && !$this->isOptionReducesResults(
                $itemsData[0]['count'],
                $this->getLayer()->getProductCollection()->getSize()
            )
        ) {
            $itemsData = $this->filterRequestDataResolver->getReducedItemsData($this, $itemsData);
        }

        switch ($this->getSetting()->getSortOptionsBy()) {
            case SortOptionsBy::NAME:
                usort($itemsData, [$this, 'sortOption']);
                break;
            case SortOptionsBy::PRODUCT_COUNT:
                $itemsData = $this->sortOptionsByCount($itemsData);
                break;
        }

        return $itemsData;
    }

    private function sortOptionsByCount(array $options): array
    {
        usort($options, static function ($left, $right) {
            return $right['count'] <=> $left['count'];
        });

        return $options;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public function sortOption($a, $b)
    {
        return strcmp($a['label'], $b['label']);
    }

    private function getFacetedData(): array
    {
        return $this->facetProvider->getFacetedData($this);
    }

    public function hasCurrentValue(): bool
    {
        return $this->filterRequestDataResolver->hasCurrentValue($this);
    }

    public function buildSearchCriteria(int $categoryId): SearchCriteria
    {
        $filter[CategoryHelper::ATTRIBUTE_CODE] = $categoryId;

        return $this->getLayer()->getProductCollection()->getMemSearchCriteria($filter);
    }

    public function getRenderCategoriesLevel(): int
    {
        return (int) $this->getSetting()->getRenderCategoriesLevel();
    }

    public function getCategoriesTreeDept(): int
    {
        return (int) $this->getSetting()->getCategoryTreeDepth();
    }

    public function isRenderAllTree(): bool
    {
        return (bool) $this->getSetting()->getRenderAllCategoriesTree();
    }

    public function isMultiselect(): bool
    {
        return $this->filterSettingResolver->isMultiselectAllowed($this);
    }

    public function useLabelsOnly(): bool
    {
        return $this->getImageDisplayMode() == CategoryTreeDisplayMode::SHOW_LABELS_ONLY;
    }

    public function useLabelsAndImages(): bool
    {
        return $this->getImageDisplayMode() == CategoryTreeDisplayMode::SHOW_LABELS_IMAGES;
    }

    public function useImagesOnly(): bool
    {
        return $this->getImageDisplayMode() == CategoryTreeDisplayMode::SHOW_IMAGES_ONLY;
    }

    public function getImageDisplayMode(): int
    {
        return (int) $this->getSetting()->getCategoryTreeDisplayMode();
    }

    public function getSetting(): FilterSettingInterface
    {
        return $this->filterSettingResolver->getFilterSetting($this);
    }

    public function getPosition(): int
    {
        return $this->configProvider->getCategoryPosition();
    }

    /**
     * Compatibility with Amasty_Amp
     */
    public function getAmpItems(): array
    {
        $data = $this->_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            $items[] = parent::_createItem($itemData['label'], $itemData['value'], $itemData['count']);
        }
        $this->_items = $items;

        return $items;
    }

    public function getFilterCode(): string
    {
        return CategoryHelper::ATTRIBUTE_CODE;
    }
}
