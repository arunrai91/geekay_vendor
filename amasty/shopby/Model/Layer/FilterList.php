<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Shopby\Helper\Config;
use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\Shopby\Model\Request;
use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Amasty\ShopbyBase\Model\FilterSettingRepository;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Magento\Catalog\Model\Layer\Search;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Amasty\Shopby\Model\Source\VisibleInCategory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;

class FilterList extends Layer\FilterList
{
    public const PLACE_SIDEBAR = 'sidebar';
    public const PLACE_TOP     = 'top';
    public const ALL_FILTERS_KEY  = 'amasty_shopby_all_filters';
    public const ONE_COLUMN_LAYOUT = '1column';
    public const VERSION24 = '2.4.0';

    /**
     * @var Http
     */
    private $request;

    /**
     * @var string
     */
    private $currentPlace;

    /**
     * @var bool
     */
    private $filtersLoaded  = false;

    /**
     * @var bool
     */
    private $filtersMatched = false;

    /**
     * @var bool
     */
    private $filtersApplied = false;

    /**
     * @var  Registry
     */
    private $registry;

    /**
     * @var Request
     */
    private $shopbyRequest;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var FilterResolver
     */
    private $filterResolver;

    /**
     * @var FilterSettingRepository
     */
    private $filterRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        FilterableAttributeListInterface $filterableAttributes,
        MagentoVersion $magentoVersion,
        Http $request,
        Registry $registry,
        Request $shopbyRequest,
        Config $config,
        LayoutInterface $layout,
        FilterResolver $filterResolver,
        FilterSettingRepository $filterRepository,
        array $filters = [],
        $place = self::PLACE_SIDEBAR
    ) {
        $this->currentPlace = $place;
        $this->request = $request;
        $this->registry = $registry;
        $this->shopbyRequest = $shopbyRequest;
        $this->config = $config;
        $this->layout = $layout;

        $version = str_replace(['-develop', 'dev-', '-beta'], '', $magentoVersion->get());
        if (version_compare($version, self::VERSION24, '>=')) {
            $params = [
                $objectManager,
                $filterableAttributes,
                $objectManager->create(\Magento\Catalog\Model\Config\LayerCategoryConfig::class),
                $filters
            ];
        } else {
            $params = [
                $objectManager,
                $filterableAttributes,
                $filters
            ];
        }

        parent::__construct(...$params);

        $this->filterResolver = $filterResolver;
        $this->filterRepository = $filterRepository;
    }

    /**
     * @param Layer $layer
     *
     * @return array|Layer\Filter\AbstractFilter[]
     */
    public function getFilters(Layer $layer)
    {
        if (!$this->filtersLoaded) {
            $filters = $this->getAllFilters($layer);
            $this->filters = $this->filterByPlace($filters, $layer);
            usort($this->filters, [$this, 'sortingByPosition']);
            $this->filtersLoaded = true;
        }
        $this->applyFilters($layer);
        $this->matchFilters($this->filters, $layer);
        return $this->filters;
    }

    /**
     * Get both top and left filters. And keep it in registry.
     *
     * @param Layer $layer
     * @return \Magento\Catalog\Model\Layer\Filter\AbstractFilter[]
     */
    public function getAllFilters(Layer $layer)
    {
        $allFilters = $this->registry->registry(self::ALL_FILTERS_KEY);
        if ($allFilters === null) {
            $allFilters = $this->generateAllFilters($layer);
            $this->registry->register(self::ALL_FILTERS_KEY, $allFilters);
        }

        $allFilters = $this->removeCategoryFilter($allFilters);

        return $allFilters;
    }

    /**
     * @param Layer $layer
     *
     * @return array
     */
    protected function generateAllFilters(Layer $layer)
    {
        $filters = parent::getFilters($layer);
        $listAdditionalFilters = $this->getAdditionalFilters($layer);
        $allFilters = $this->insertAdditionalFilters($filters, $listAdditionalFilters);
        $this->filterResolver->preloadFiltersSettings($allFilters);

        return $allFilters;
    }

    /**
     * @param array $allFilters
     *
     * @return array
     */
    protected function removeCategoryFilter($allFilters)
    {
        if (!$this->config->isCategoryFilterEnabled()) {
            foreach ($allFilters as $id => $filter) {
                if ($filter instanceof Category) {
                    unset($allFilters[$id]);
                }
            }
        }

        return $allFilters;
    }

    /**
     * @param array $filters
     * @param Layer $layer
     * @return array
     */
    protected function filterByPlace(array $filters, Layer $layer)
    {
        $filters = array_filter($filters, function ($filter) use ($layer) {
            if ($this->isOneColumnLayout($layer)) {
                //Move all filters to open place in one column design
                return $this->currentPlace == self::PLACE_SIDEBAR;
            }

            $position = $this->getFilterBlockPosition($filter);
            return $position == FilterPlacedBlock::POSITION_BOTH
                || ($position == FilterPlacedBlock::POSITION_SIDEBAR && $this->currentPlace == self::PLACE_SIDEBAR)
                || ($position == FilterPlacedBlock::POSITION_TOP && $this->currentPlace == self::PLACE_TOP);
        });

        return $filters;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return int
     */
    protected function getFilterBlockPosition(FilterInterface $filter)
    {
        return $this->filterResolver->resolveByFilter($filter)->getBlockPosition();
    }

    /**
     * @param Layer $layer
     * @return bool
     */
    protected function isOneColumnLayout(Layer $layer)
    {
        return $this->getPageLayout($layer) == self::ONE_COLUMN_LAYOUT;
    }

    /**
     * @param Layer $layer
     * @return string
     */
    private function getPageLayout(Layer $layer)
    {
        return !$layer instanceof Search && $layer->getCurrentCategory()->getData('page_layout')
            ? $layer->getCurrentCategory()->getData('page_layout')
            : $this->layout->getUpdate()->getPageLayout();
    }

    /**
     * @param array $listFilters
     * @param Layer $layer
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function matchFilters(array $listFilters, Layer $layer)
    {
        if ($this->filtersMatched) {
            return false;
        }

        $matchedFilters = [];
        foreach ($listFilters as $filter) {
            $setting = $this->filterResolver->resolveByFilter($filter);
            if (!$this->checkFilterVisibility($setting, $layer->getCurrentCategory()->getId())) {
                continue;
            }

            if (!$this->checkFilterByDependency($setting)) {
                continue;
            }

            $matchedFilters[] = $filter;
        }

        $this->filtersMatched = true;
        $this->filters = $matchedFilters;

        return true;
    }

    /**
     * @param FilterSettingInterface $setting
     * @param $currentCategoryId
     *
     * @return bool
     */
    protected function checkFilterVisibility(FilterSettingInterface $setting, $currentCategoryId)
    {
        $visible = true;
        if ($setting->getVisibleInCategories() === VisibleInCategory::ONLY_IN_SELECTED_CATEGORIES
            && !in_array($currentCategoryId, $setting->getCategoriesFilter())
        ) {
            $visible = false;
        }

        if ($setting->getVisibleInCategories() === VisibleInCategory::HIDE_IN_SELECTED_CATEGORIES
            && in_array($currentCategoryId, $setting->getCategoriesFilter())
        ) {
            $visible = false;
        }

        return $visible;
    }

    /**
     * @param FilterSettingInterface $setting
     *
     * @return bool
     */
    protected function checkFilterByDependency(FilterSettingInterface $setting)
    {
        $matched = true;
        if ($attributesFilter = $setting->getAttributesFilter()) {
            $stateAttributes = $this->getStateAttributesIds();
            $intersects = array_intersect($attributesFilter, $stateAttributes);
            if (!$intersects) {
                $matched = false;
            }
        }

        if ($attributesOptionsFilter = $setting->getAttributesOptionsFilter()) {
            $stateAttributesOptions = $this->getActiveOptionIds();
            $intersects = array_intersect($attributesOptionsFilter, $stateAttributesOptions);
            if (!$intersects) {
                $matched = false;
            }
        }

        return $matched;
    }

    /**
     * At this point filters could not be applied (especially at search page).
     *
     * @param Layer $layer
     */
    private function applyFilters(Layer $layer): void
    {
        if ($this->filtersApplied || $layer->getProductCollection()->isLoaded()) {
            return;
        }
        $this->filtersApplied = true;

        foreach ($this->getAllFilters($layer) as $filter) {
            //filter has multiply applying prevention mechanism
            $filter->apply($this->request);
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStateAttributesIds()
    {
        $ids = [];

        foreach ($this->shopbyRequest->getRequestParams() as $key => $param) {
            $filterModelId = $this->getFilterModelId($key);
            if ($filterModelId) {
                $ids[] = $filterModelId;
            }
        }

        return array_unique($ids);
    }

    /**
     * @param string $key
     *
     * @return int
     */
    protected function getFilterModelId($key)
    {
        $filter = $this->filterRepository->loadByAttributeCode($key);
        $filterModel = $filter ? $filter->getAttributeModel() : false;

        return $filterModel ? $filterModel->getId() : 0;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getActiveOptionIds()
    {
        $ids = [];

        foreach ($this->shopbyRequest->getRequestParams() as $param) {
            if (isset($param[0])) {
                $ids[] = explode(',', $param[0]);
            }
        }

        if (count($ids)) {
            $ids = array_unique(array_merge(...$ids));
        }

        return $ids;
    }

    /**
     * @param Layer $layer
     *
     * @return array
     */
    protected function getAdditionalFilters(Layer $layer)
    {
        $additionalFilters = [];
        if ($this->isCustomFilterEnabled('stock') && $this->config->isEnabledShowOutOfStock()) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\Stock::class,
                ['layer' => $layer]
            );
        }

        if ($this->isCustomFilterEnabled('rating')) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\Rating::class,
                ['layer' => $layer]
            );
        }

        if ($this->isCustomFilterEnabled('am_is_new')) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\IsNew::class,
                ['layer' => $layer]
            );
        }

        if ($this->isCustomFilterEnabled('am_on_sale')) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\OnSale::class,
                ['layer' => $layer]
            );
        }

        return $additionalFilters;
    }

    /**
     * @param string $filterKey
     * @return bool
     */
    protected function isCustomFilterEnabled($filterKey)
    {
        return (bool)$this->config->getModuleConfig($filterKey . '_filter/enabled');
    }

    /**
     * @param $listStandartFilters
     * @param $listAdditionalFilters
     * @return array
     */
    protected function insertAdditionalFilters($listStandartFilters, $listAdditionalFilters)
    {
        if (count($listAdditionalFilters) == 0) {
            return $listStandartFilters;
        }

        return array_merge($listStandartFilters, $listAdditionalFilters);
    }

    /**
     * @param FilterInterface $first
     * @param FilterInterface $second
     * @return int
     */
    public function sortingByPosition(FilterInterface $first, FilterInterface $second): int
    {
        $settingA = $this->filterResolver->resolveByFilter($first);
        $settingB = $this->filterResolver->resolveByFilter($second);

        if ($isLocalPositionA = ($this->getFilterBlockPosition($first) === FilterPlacedBlock::POSITION_BOTH)) {
            $positionA = $this->getFilterLocalPosition($settingA);
        } else {
            $positionA = $settingA->getPosition() ?: $this->getFilterPosition($first);
        }

        if ($isLocalPositionB = ($this->getFilterBlockPosition($second) === FilterPlacedBlock::POSITION_BOTH)) {
            $positionB = $this->getFilterLocalPosition($settingB);
        } else {
            $positionB = $settingB->getPosition() ?: $this->getFilterPosition($second);
        }

        if ($isLocalPositionA && $isLocalPositionB && $positionA === $positionB) {
            return $this->getFilterPosition($first) <=> $this->getFilterPosition($second);
        }

        return $positionA <=> $positionB;
    }

    private function getFilterLocalPosition(FilterSettingInterface $setting): int
    {
        if ($this->currentPlace === self::PLACE_TOP) {
            $position = $setting->getTopPosition();
        } else {
            $position = $setting->getSidePosition();
        }

        return (int) $position;
    }

    /**
     * @param FilterInterface $filter
     * @return int
     */
    public function getFilterPosition(FilterInterface $filter): int
    {
        if ($filter->hasAttributeModel()) {
            $position = $filter->getAttributeModel()->getPosition();
        } else {
            $position = $filter->getPosition();
        }

         return (int) $position;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return string
     */
    protected function getAttributeFilterClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $filterClassName = parent::getAttributeFilterClass($attribute);

        if ($attribute->getBackendType() === 'decimal' && $attribute->getAttributeCode() !== 'price') {
            $filterClassName = $this->filterTypes[self::DECIMAL_FILTER];
        }

        return $filterClassName;
    }
}
