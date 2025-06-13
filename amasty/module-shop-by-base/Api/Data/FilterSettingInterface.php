<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api\Data;

interface FilterSettingInterface
{
    public const FILTER_SETTING = 'filter_setting';

    public const CACHE_TAG = 'amshopby_filter_setting';

    public const FILTER_SETTING_ID = 'setting_id';
    public const FILTER_CODE = 'filter_code';
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const DISPLAY_MODE = 'display_mode';
    public const IS_MULTISELECT = 'is_multiselect';
    public const IS_SEO_SIGNIFICANT = 'is_seo_significant';
    public const INDEX_MODE = 'index_mode';
    public const FOLLOW_MODE = 'follow_mode';
    public const REL_NOFOLLOW = 'rel_nofollow';
    public const EXPAND_VALUE = 'is_expanded';
    public const SORT_OPTIONS_BY = 'sort_options_by';
    public const SHOW_PRODUCT_QUANTITIES = 'show_product_quantities';
    public const IS_SHOW_SEARCH_BOX = 'is_show_search_box';
    public const NUMBER_UNFOLDED_OPTIONS = 'number_unfolded_options';
    public const TOOLTIP = 'tooltip';
    public const ADD_FROM_TO_WIDGET = 'add_from_to_widget';
    public const IS_USE_AND_LOGIC = 'is_use_and_logic';
    public const VISIBLE_IN_CATEGORIES = 'visible_in_categories';
    public const CATEGORIES_FILTER = 'categories_filter';
    public const ATTRIBUTES_FILTER = 'attributes_filter';
    public const ATTRIBUTES_OPTIONS_FILTER = 'attributes_options_filter';
    public const BLOCK_POSITION = 'block_position';
    public const SHOW_ICONS_ON_PRODUCT = 'show_icons_on_product';
    public const UNITS_LABEL = 'units_label';
    public const USE_CURRENCY_SYMBOL = 'units_label_use_currency_symbol';
    public const SHOW_FEATURED_ONLY = 'show_featured_only';
    public const CATEGORY_TREE_DISPLAY_MODE = 'category_tree_display_mode';
    public const LIMIT_OPTIONS_SHOW_SEARCH_BOX = 'limit_options_show_search_box';
    public const TOP_POSITION = 'top_position';
    public const SIDE_POSITION = 'side_position';
    public const POSITION = 'position';
    public const POSITION_LABEL = 'position_label';
    public const SLIDER_STEP = 'slider_step';
    public const SLIDER_MIN = 'slider_min';
    public const SLIDER_MAX = 'slider_max';
    public const ATTRIBUTE_URL_ALIAS = 'attribute_url_alias';
    public const CATEGORY_TREE_DEPTH = 'category_tree_depth';
    public const SUBCATEGORIES_VIEW = 'subcategories_view';
    public const SUBCATEGORIES_EXPAND = 'subcategories_expand';
    public const RENDER_ALL_CATEGORIES_TREE = 'render_all_categories_tree';
    public const RENDER_CATEGORIES_LEVEL = 'render_categories_level';
    public const HIDE_ZEROS = 'hide_zeros';
    public const RANGE_ALGORITHM = 'range_algorithm';
    public const RANGE_STEP = 'range_step';
    public const ATTRIBUTE_ID = 'attribute_id';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getDisplayMode();

    /**
     * @return int
     */
    public function getFollowMode();

    /**
     * @deprecated 2.16.0 use setAttributeCode
     * @return string|null
     */
    public function getFilterCode();

    /**
     * @return string
     */
    public function getAttributeCode(): ?string;

    /**
     * @return int
     */
    public function getIndexMode();

    /**
     * @return int
     */
    public function getRelNofollow();

    /**
     * @return bool|null
     */
    public function getAddFromToWidget();

    /**
     * @return bool
     */
    public function isMultiselect();

    /**
     * @return int
     */
    public function getSeoSignificant(): int;

    /**
     * @return bool|null
     */
    public function isExpanded();

    /**
     * @return int
     */
    public function getSortOptionsBy();

    /**
     * @return int
     */
    public function getShowProductQuantities();

    /**
     * @param int $optionsCount
     * @return bool
     */
    public function isShowSearchBox($optionsCount);

    /**
     * @return mixed
     */
    public function getNumberUnfoldedOptions();

    /**
     * @return bool
     */
    public function isUseAndLogic();

    /**
     * @return string
     */
    public function getTooltip();

    /**
     * @return string
     */
    public function getVisibleInCategories();

    /**
     * @return mixed
     */
    public function getCategoriesFilter();

    /**
     * @return mixed
     */
    public function getAttributesFilter();

    /**
     * @return mixed
     */
    public function getAttributesOptionsFilter();

    /**
     * @return int
     */
    public function getBlockPosition();

    /**
     * @return mixed
     */
    public function getShowIconsOnProduct();

    /**
     * @return string
     */
    public function getUnitsLabel();

    /**
     * @return int
     */
    public function getUnitsLabelUseCurrencySymbol();

    /**
     * @return int
     */
    public function getCategoryTreeDisplayMode();

    /**
     * @return int
     */
    public function getTopPosition(): int;

    /**
     * @return int
     */
    public function getSidePosition(): int;

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @param int $id
     * @return FilterSettingInterface
     */
    public function setId($id);

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setDisplayMode($displayMode);

    /**
     * @param int $indexMode
     * @return FilterSettingInterface
     */
    public function setIndexMode($indexMode);

    /**
     * @param int $relNofollow
     * @return FilterSettingInterface
     */
    public function setRelNofollow($relNofollow);

    /**
     * @param int $followMode
     * @return FilterSettingInterface
     */
    public function setFollowMode($followMode);

    /**
     * @param bool $isMultiselect
     * @return FilterSettingInterface
     */
    public function setIsMultiselect($isMultiselect);

    /**
     * @param int $seoSignificant
     * @return void
     */
    public function setSeoSignificant(int $seoSignificant): void;

    /**
     * @param bool $isExpanded
     *
     * @return FilterSettingInterface
     */
    public function setIsExpanded($isExpanded);

    /**
     * @deprecated 2.16.0 use setAttributeCode
     * @param string $filterCode
     * @return FilterSettingInterface
     */
    public function setFilterCode($filterCode);

    /**
     * @param string $filterCode
     * @return void
     */
    public function setAttributeCode(string $filterCode): void;

    /**
     * @param int $sortOptionsBy
     * @return FilterSettingInterface
     */
    public function setSortOptionsBy($sortOptionsBy);

    /**
     * @param int $showProductQuantities
     * @return FilterSettingInterface
     */
    public function setShowProductQuantities($showProductQuantities);

    /**
     * @param int|null $isShowSearchBox
     * @return void
     */
    public function setIsShowSearchBox(?int $isShowSearchBox): void;

    /**
     * @param int $numberOfUnfoldedOptions
     * @return FilterSettingInterface
     */
    public function setNumberUnfoldedOptions($numberOfUnfoldedOptions);

    /**
     * @param string $tooltip
     *
     * @return FilterSettingInterface
     */
    public function setTooltip($tooltip);

    /**
     * @param string $visibleInCategories
     * @return FilterSettingInterface
     */
    public function setVisibleInCategories($visibleInCategories);

    /**
     * @param array $categoriesFilter
     * @return FilterSettingInterface
     */
    public function setCategoriesFilter($categoriesFilter);

    /**
     * @param array $attributesFilter
     * @return FilterSettingInterface
     */
    public function setAttributesFilter($attributesFilter);

    /**
     * @param array $attributesOptionsFilter
     * @return FilterSettingInterface
     */
    public function setAttributesOptionsFilter($attributesOptionsFilter);

    /**
     * @param bool $addFromToWidget
     *
     * @return FilterSettingInterface
     */
    public function setAddFromToWidget($addFromToWidget);

    /**
     * @param bool $isUseAndLogic
     *
     * @return FilterSettingInterface
     */
    public function setIsUseAndLogic($isUseAndLogic);

    /**
     * @param int $blockPosition
     *
     * positions may see in \Amasty\ShopbyBase\Model\Source\FilterPlacedBlock
     * @return FilterSettingInterface
     */
    public function setBlockPosition($blockPosition);

    /**
     * @param string|array $isShowLinks
     * @return FilterSettingInterface
     */
    public function setShowIconsOnProduct($isShowLinks);

    /**
     * @param string $label
     * @return void
     */
    public function setUnitsLabel(string $label): void;

    /**
     * @param int $useCurrency
     * @return FilterSettingInterface
     */
    public function setUnitsLabelUseCurrencySymbol($useCurrency);

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setCategoryTreeDisplayMode($displayMode);

    /**
     * @return int
     */
    public function getPositionLabel(): int;

    /**
     * @param int $positionLabel
     * @return void
     */
    public function setPositionLabel(int $positionLabel): void;

    /**
     * @return float
     */
    public function getSliderMin(): float;

    /**
     * @param float $min
     * @return void
     */
    public function setSliderMin(float $min): void;

    /**
     * @return float
     */
    public function getSliderMax(): float;

    /**
     * @param float $max
     * @return void
     */
    public function setSliderMax(float $max): void;

    /**
     * @param int $topPosition
     * @return void
     */
    public function setTopPosition(int $topPosition): void;

    /**
     * @param int $sidePosition
     * @return void
     */
    public function setSidePosition(int $sidePosition): void;

    /**
     * @param int $position
     * @return void
     */
    public function setPosition(int $position): void;

    /**
     * @return int|null
     */
    public function getLimitOptionsShowSearchBox(): ?int;

    /**
     * @param int|null $limit
     * @return void
     */
    public function setLimitOptionsShowSearchBox(?int $limit): void;

    /**
     * @return int|null
     */
    public function getIsShowSearchBox(): ?int;

    /**
     * @param string $alias
     * @return void
     */
    public function setAttributeUrlAlias(?string $alias): void;

    /**
     * @return string|null
     */
    public function getAttributeUrlAlias(): ?string;

    /**
     * @param float|null $step
     * @return void
     */
    public function setSliderStep(?float $step): void;

    /**
     * @return float|null
     */
    public function getSliderStep(): ?float;

    /**
     * @return bool|null
     */
    public function getRenderAllCategoriesTree(): ?bool;

    /**
     * @param bool|null $renderAllCategoriesTree
     * @return void
     */
    public function setRenderAllCategoriesTree(?bool $renderAllCategoriesTree): void;

    /**
     * @return int|null
     */
    public function getSubcategoriesView(): ?int;

    /**
     * @param int|null $subcategoriesView
     * @return void
     */
    public function setSubcategoriesView(?int $subcategoriesView): void;

    /**
     * @return int|null
     */
    public function getRenderCategoriesLevel(): ?int;

    /**
     * @param int|null $level
     * @return void
     */
    public function setRenderCategoriesLevel(?int $level): void;

    /**
     * @return int|null
     */
    public function getCategoryTreeDepth(): ?int;

    /**
     * @param int|null $treeDepth
     * @return void
     */
    public function setCategoryTreeDepth(?int $treeDepth): void;

    /**
     * @return int|null
     */
    public function getSubcategoriesExpand(): ?int;

    /**
     * @param int|null $subcategoriesExpand
     * @return void
     */
    public function setSubcategoriesExpand(?int $subcategoriesExpand): void;

    /**
     * If the price value in the filter is equivalent to an int value, then cut out .00
     *
     * @return bool
     */
    public function getHideZeros(): bool;

    /**
     * @param bool $hideZeros
     * @return void
     */
    public function setHideZeros(bool $hideZeros): void;

    /**
     * For price attributes in Ranges Display Mode.
     * @see \Amasty\Shopby\Model\Source\RangeAlgorithm
     *
     * @return int
     */
    public function getRangeAlgorithm(): ?int;

    /**
     * For price attributes in Ranges Display Mode.
     * @see \Amasty\Shopby\Model\Source\RangeAlgorithm
     *
     * @param int $rangeAlgorithm
     * @return void
     */
    public function setRangeAlgorithm(int $rangeAlgorithm): void;

    /**
     * For price attributes in Ranges Display Mode and Custom Range Algorithm.
     *
     * @return float
     */
    public function getRangeStep(): ?float;

    /**
     * For price attributes in Ranges Display Mode and Custom Range Algorithm.
     *
     * @param float $rangeStep
     * @return void
     */
    public function setRangeStep(float $rangeStep): void;

    /**
     * @return int|null
     */
    public function getAttributeId(): ?int;

    /**
     * @param int $attributeId
     * @return void
     */
    public function setAttributeId(int $attributeId): void;
}
