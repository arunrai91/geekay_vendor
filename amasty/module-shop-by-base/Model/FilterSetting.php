<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting\IsAddNofollow;
use Amasty\ShopbyBase\Model\Source\DisplayMode;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;

class FilterSetting extends \Magento\Framework\Model\AbstractModel implements FilterSettingInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'amshopby_filter_setting';

    /**
     * Protected FilterSetting constructor
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyBase\Model\ResourceModel\FilterSetting::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::FILTER_SETTING_ID);
    }

    /**
     * @return int
     */
    public function getDisplayMode()
    {
        $displayMode = (int) $this->getData(self::DISPLAY_MODE);

        return $displayMode == DisplayMode::MODE_DROPDOWN ? DisplayMode::MODE_DEFAULT : $displayMode;
    }

    /**
     * @deprecated 2.16.0 use getAttributeCode
     * @return string
     */
    public function getFilterCode()
    {
        return $this->getData(self::FILTER_CODE);
    }

    /**
     * @return string
     */
    public function getAttributeCode(): ?string
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @return int
     */
    public function getFollowMode()
    {
        return (int) $this->getData(self::FOLLOW_MODE);
    }

    /**
     * @return int
     */
    public function getRelNofollow()
    {
        return (int) $this->getData(self::REL_NOFOLLOW);
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isAddNofollow()
    {
        return ObjectManager::getInstance()->get(IsAddNofollow::class)->execute(
            $this->getRelNofollow(),
            $this->getFollowMode()
        );
    }

    /**
     * @return bool|null
     */
    public function getAddFromToWidget()
    {
        return $this->getData(self::ADD_FROM_TO_WIDGET);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getAttributeCode()];
    }

    /**
     * @return int
     */
    public function getIndexMode()
    {
        return (int) $this->getData(self::INDEX_MODE);
    }

    /**
     * @param string $currencySymbol
     * @return string
     */
    public function getUnitsLabel($currencySymbol = '')
    {
        return $this->getUnitsLabelUseCurrencySymbol() ? $currencySymbol : parent::getUnitsLabel();
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        return (bool) $this->getData(self::IS_MULTISELECT);
    }

    public function getSeoSignificant(): int
    {
        return (int) $this->getData(self::IS_SEO_SIGNIFICANT);
    }

    /**
     * @return bool
     */
    public function isExpanded()
    {
        return $this->getData(self::EXPAND_VALUE);
    }

    /**
     * @return int
     */
    public function getSortOptionsBy()
    {
        return $this->getData(self::SORT_OPTIONS_BY);
    }

    /**
     * @return int
     */
    public function getShowProductQuantities()
    {
        return (int) $this->getData(self::SHOW_PRODUCT_QUANTITIES);
    }

    /**
     * @return bool
     */
    public function isShowSearchBox($optionsCount)
    {
        return $this->getData(self::IS_SHOW_SEARCH_BOX)
            && (!$this->getLimitOptionsShowSearchBox() || $optionsCount > $this->getLimitOptionsShowSearchBox());
    }

    /**
     * @return mixed
     */
    public function getNumberUnfoldedOptions()
    {
        return $this->getData(self::NUMBER_UNFOLDED_OPTIONS);
    }

    /**
     * @return mixed
     */
    public function getTooltip()
    {
        return $this->getData(self::TOOLTIP);
    }

    /**
     * @return string
     */
    public function getVisibleInCategories()
    {
        return $this->getData(self::VISIBLE_IN_CATEGORIES);
    }

    /**
     * @return mixed
     */
    public function getCategoriesFilter()
    {
        $this->getResource()->lookupCategoriesFilter($this);
        return $this->getData(self::CATEGORIES_FILTER);
    }

    /**
     * @return mixed
     */
    public function getAttributesFilter()
    {
        $this->getResource()->lookupAttributesFilter($this);
        return $this->getData(self::ATTRIBUTES_FILTER);
    }

    /**
     * @return mixed
     */
    public function getAttributesOptionsFilter()
    {
        $this->getResource()->lookupAttributesOptionsFilter($this);
        return $this->getData(self::ATTRIBUTES_OPTIONS_FILTER);
    }

    /**
     * @return bool
     */
    public function isUseAndLogic()
    {
        return $this->getData(self::IS_USE_AND_LOGIC);
    }

    /**
     * @return int
     */
    public function getBlockPosition()
    {
        return (int) $this->getData(self::BLOCK_POSITION);
    }

    /**
     * @return bool
     */
    public function getShowIconsOnProduct()
    {
        return $this->getData(self::SHOW_ICONS_ON_PRODUCT);
    }

    /**
     * @return bool
     */
    public function getUnitsLabelUseCurrencySymbol()
    {
        return $this->getData(self::USE_CURRENCY_SYMBOL);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::FILTER_SETTING_ID, $id);
    }

    /**
     * @param int $displayMode
     * @return $this
     */
    public function setDisplayMode($displayMode)
    {
        return $this->setData(self::DISPLAY_MODE, $displayMode);
    }

    /**
     * @param int $displayMode
     * @return $this
     */
    public function setCategoryTreeDisplayMode($displayMode)
    {
        $this->setData(self::CATEGORY_TREE_DISPLAY_MODE, $displayMode);
        return $this;
    }

    /**
     * @return int
     */
    public function getPositionLabel(): int
    {
        return (int) $this->getData(self::POSITION_LABEL);
    }

    /**
     * @param int $positionLabel
     */
    public function setPositionLabel(int $positionLabel): void
    {
        $this->setData(self::POSITION_LABEL, $positionLabel);
    }

    /**
     * @deprecated 2.16.0 use setAttributeCode
     * @param string $filterCode
     * @return $this
     */
    public function setFilterCode($filterCode)
    {
        return $this->setData(self::FILTER_CODE, $filterCode);
    }

    /**
     * @param string $filterCode
     */
    public function setAttributeCode(string $filterCode): void
    {
        $this->setData(self::ATTRIBUTE_CODE, $filterCode);
    }

    /**
     * @param int $indexMode
     * @return $this
     */
    public function setIndexMode($indexMode)
    {
        return $this->setData(self::INDEX_MODE, $indexMode);
    }

    /**
     * @param int $followMode
     * @return $this
     */
    public function setFollowMode($followMode)
    {
        return $this->setData(self::FOLLOW_MODE, $followMode);
    }

    /**
     * @param int $relNofollow
     * @return $this
     */
    public function setRelNofollow($relNofollow)
    {
        return $this->setData(self::REL_NOFOLLOW, $relNofollow);
    }

    /**
     * @param bool $isMultiselect
     * @return $this
     */
    public function setIsMultiselect($isMultiselect)
    {
        return $this->setData(self::IS_MULTISELECT, $isMultiselect);
    }

    public function setSeoSignificant(int $seoSignificant): void
    {
        $this->setData(self::IS_SEO_SIGNIFICANT, $seoSignificant);
    }

    /**
     * @param bool $isExpanded
     * @return $this
     */
    public function setIsExpanded($isExpanded)
    {
        return $this->setData(self::EXPAND_VALUE, $isExpanded);
    }

    /**
     * @param bool $addFromToWidget
     *
     * @return FilterSettingInterface
     */
    public function setAddFromToWidget($addFromToWidget)
    {
        return $this->setData(self::ADD_FROM_TO_WIDGET, $addFromToWidget);
    }

    /**
     * @param int $sortOptionsBy
     *
     * @return FilterSettingInterface
     */
    public function setSortOptionsBy($sortOptionsBy)
    {
        return $this->setData(self::SORT_OPTIONS_BY, $sortOptionsBy);
    }

    /**
     * @param int $showProductQuantities
     *
     * @return FilterSettingInterface
     */
    public function setShowProductQuantities($showProductQuantities)
    {
        return $this->setData(self::SHOW_PRODUCT_QUANTITIES, $showProductQuantities);
    }

    /**
     * @param int|null $isShowSearchBox
     */
    public function setIsShowSearchBox(?int $isShowSearchBox): void
    {
        $this->setData(self::IS_SHOW_SEARCH_BOX, $isShowSearchBox);
    }

    /**
     * @param int $numberOfUnfoldedOptions
     *
     * @return FilterSettingInterface
     */
    public function setNumberUnfoldedOptions($numberOfUnfoldedOptions)
    {
        return $this->setData(self::NUMBER_UNFOLDED_OPTIONS, $numberOfUnfoldedOptions);
    }

    /**
     * @param string $tooltip
     *
     * @return $this
     */
    public function setTooltip($tooltip)
    {
        return $this->setData(self::TOOLTIP, $tooltip);
    }

    /**
     * @param string $visibleInCategories
     * @return $this
     */
    public function setVisibleInCategories($visibleInCategories)
    {
        return $this->setData(self::VISIBLE_IN_CATEGORIES, $visibleInCategories);
    }

    /**
     * @param array $categoriesFilter
     * @return $this
     */
    public function setCategoriesFilter($categoriesFilter)
    {
        return $this->setData(self::CATEGORIES_FILTER, $categoriesFilter);
    }

    /**
     * @param array $attributesFilter
     * @return $this
     */
    public function setAttributesFilter($attributesFilter)
    {
        return $this->setData(self::ATTRIBUTES_FILTER, $attributesFilter);
    }

    /**
     * @param array $attributesOptionsFilter
     * @return $this
     */
    public function setAttributesOptionsFilter($attributesOptionsFilter)
    {
        return $this->setData(self::ATTRIBUTES_OPTIONS_FILTER, $attributesOptionsFilter);
    }

    /**
     * @param bool $isUseAndLogic
     *
     * @return $this
     */
    public function setIsUseAndLogic($isUseAndLogic)
    {
        return $this->setData(self::IS_USE_AND_LOGIC, $isUseAndLogic);
    }

    public function getSliderMin(): float
    {
        return (float) $this->getData(self::SLIDER_MIN);
    }

    public function getSliderMax(): float
    {
        return (float) $this->getData(self::SLIDER_MAX);
    }

    public function setSliderMin(float $value): void
    {
        $this->setData(self::SLIDER_MIN, $value);
    }

    public function setSliderMax(float $value): void
    {
        $this->setData(self::SLIDER_MAX, $value);
    }

    /**
     * @param int $blockPosition
     *
     * @return $this
     */
    public function setBlockPosition($blockPosition)
    {
        return $this->setData(self::BLOCK_POSITION, $blockPosition);
    }

    /**
     * @param bool $isShowLinks
     * @return $this
     */
    public function setShowIconsOnProduct($isShowLinks)
    {
        return $this->setData(self::SHOW_ICONS_ON_PRODUCT, $isShowLinks);
    }

    public function setAttributeModel(?Attribute $attribute): void
    {
        $this->setData('attribute_model', $attribute);
    }

    public function getAttributeModel(): ?Attribute
    {
        return $this->getData('attribute_model');
    }

    public function setUnitsLabel(string $label): void
    {
        $this->setData(self::UNITS_LABEL, $label);
    }

    /**
     * @param int $useCurrency
     * @return $this
     */
    public function setUnitsLabelUseCurrencySymbol($useCurrency)
    {
        return $this->setData(self::USE_CURRENCY_SYMBOL, $useCurrency);
    }

    /**
     * @return int
     */
    public function getCategoryTreeDisplayMode()
    {
        return $this->getData(self::CATEGORY_TREE_DISPLAY_MODE);
    }

    /**
     * @return int|null
     */
    public function getLimitOptionsShowSearchBox(): ?int
    {
        $data = $this->getDataByKey(self::LIMIT_OPTIONS_SHOW_SEARCH_BOX);

        return $data === null ? null : (int) $data;
    }

    /**
     * @param int|null $limitOptionsShowSearchBox
     */
    public function setLimitOptionsShowSearchBox(?int $limitOptionsShowSearchBox): void
    {
        $this->setData(self::LIMIT_OPTIONS_SHOW_SEARCH_BOX, $limitOptionsShowSearchBox);
    }

    public function getTopPosition(): int
    {
        return (int) $this->getDataByKey(self::TOP_POSITION);
    }

    public function getSidePosition(): int
    {
        return (int) $this->getDataByKey(self::SIDE_POSITION);
    }

    public function getPosition(): int
    {
        return (int) $this->getDataByKey(self::POSITION);
    }

    public function setTopPosition(int $topPosition): void
    {
        $this->setData(self::TOP_POSITION, $topPosition);
    }

    public function setSidePosition(int $sidePosition): void
    {
        $this->setData(self::SIDE_POSITION, $sidePosition);
    }

    public function setPosition(int $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    public function getIsShowSearchBox(): ?int
    {
        return $this->getDataByKey(self::IS_SHOW_SEARCH_BOX) !== null
            ? (int) $this->getDataByKey(self::IS_SHOW_SEARCH_BOX)
            : null;
    }

    public function setAttributeUrlAlias(?string $alias): void
    {
        $this->setData(self::ATTRIBUTE_URL_ALIAS, $alias);
    }

    public function getAttributeUrlAlias(): ?string
    {
        return $this->getDataByKey(self::ATTRIBUTE_URL_ALIAS);
    }

    public function setSliderStep(?float $step): void
    {
        $this->setData(self::SLIDER_STEP, $step);
    }

    public function getSliderStep(): ?float
    {
        return $this->getDataByKey(self::SLIDER_STEP) !== null
            ? (float) $this->getDataByKey(self::SLIDER_STEP)
            : null;
    }

    public function getRenderAllCategoriesTree(): ?bool
    {
        return $this->getDataByKey(self::RENDER_ALL_CATEGORIES_TREE) !== null
            ? (bool) $this->getDataByKey(self::RENDER_ALL_CATEGORIES_TREE)
            : null;
    }

    public function setRenderAllCategoriesTree(?bool $renderAllCategoriesTree): void
    {
        $this->setData(self::RENDER_ALL_CATEGORIES_TREE, $renderAllCategoriesTree);
    }

    public function getSubcategoriesView(): ?int
    {
        return $this->getDataByKey(self::SUBCATEGORIES_VIEW) !== null
            ? (int) $this->getDataByKey(self::SUBCATEGORIES_VIEW)
            : null;
    }

    public function setSubcategoriesView(?int $subcategoriesView): void
    {
        $this->setData(self::SUBCATEGORIES_VIEW, $subcategoriesView);
    }

    public function getRenderCategoriesLevel(): ?int
    {
        return $this->getDataByKey(self::RENDER_CATEGORIES_LEVEL) !== null
            ? (int) $this->getDataByKey(self::RENDER_CATEGORIES_LEVEL)
            : null;
    }

    public function setRenderCategoriesLevel(?int $level): void
    {
        $this->setData(self::RENDER_CATEGORIES_LEVEL, $level);
    }

    public function getCategoryTreeDepth(): ?int
    {
        return $this->getDataByKey(self::CATEGORY_TREE_DEPTH) !== null
            ? (int) $this->getDataByKey(self::CATEGORY_TREE_DEPTH)
            : null;
    }

    public function setCategoryTreeDepth(?int $treeDepth): void
    {
        $this->setData(self::CATEGORY_TREE_DEPTH, $treeDepth);
    }

    public function getSubcategoriesExpand(): ?int
    {
        return $this->getDataByKey(self::SUBCATEGORIES_EXPAND) !== null
            ? (int) $this->getDataByKey(self::SUBCATEGORIES_EXPAND)
            : null;
    }

    public function setSubcategoriesExpand(?int $subcategoriesExpand): void
    {
        $this->setData(self::SUBCATEGORIES_EXPAND, $subcategoriesExpand);
    }

    public function getHideZeros(): bool
    {
        return (bool)$this->getData(self::HIDE_ZEROS);
    }

    public function setHideZeros(bool $hideZeros): void
    {
        $this->setData(self::HIDE_ZEROS, $hideZeros);
    }

    public function getRangeAlgorithm(): ?int
    {
        return $this->getData(self::RANGE_ALGORITHM) !== null
            ? (int)$this->getData(self::RANGE_ALGORITHM)
            : null;
    }

    public function setRangeAlgorithm(int $rangeAlgorithm): void
    {
        $this->setData(self::RANGE_ALGORITHM, $rangeAlgorithm);
    }

    public function getRangeStep(): ?float
    {
        return $this->getData(self::RANGE_STEP) !== null
            ? (float)$this->getData(self::RANGE_STEP)
            : null;
    }

    public function setRangeStep(float $rangeStep): void
    {
        $this->setData(self::RANGE_STEP, $rangeStep);
    }

    public function getAttributeId(): ?int
    {
        if ($this->hasData(self::ATTRIBUTE_ID)) {
            return (int)$this->getData(self::ATTRIBUTE_ID);
        }

        return null;
    }

    public function setAttributeId(int $attributeId): void
    {
        $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }
}
