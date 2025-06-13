<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FilterSettingProxy implements FilterSettingInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'amshopby_filter_setting';

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var FilterSettingInterface|null
     */
    private $subject = null;

    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        string $attributeCode
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->attributeCode = $attributeCode;
    }

    private function init()
    {
        $this->subject = $this->filterSettingRepository->loadByAttributeCode($this->attributeCode);
    }

    public function getSubject(): FilterSettingInterface
    {
        if ($this->subject === null) {
            $this->init();
        }

        return $this->subject;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getSubject()->getId();
    }

    /**
     * @return int
     */
    public function getDisplayMode()
    {
        return $this->getSubject()->getDisplayMode();
    }

    /**
     * @deprecated 2.16.0 use getAttributeCode
     * @return string
     */
    public function getFilterCode()
    {
        return $this->getSubject()->getFilterCode();
    }

    /**
     * @return null|string
     */
    public function getAttributeCode(): ?string
    {
        return $this->getSubject()->getAttributeCode();
    }

    /**
     * @return int
     */
    public function getFollowMode()
    {
        return $this->getSubject()->getFollowMode();
    }

    /**
     * @return int
     */
    public function getRelNofollow()
    {
        return $this->getSubject()->getRelNofollow();
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isAddNofollow()
    {
        return $this->getSubject()->isAddNofollow();
    }

    /**
     * @return bool|null
     */
    public function getAddFromToWidget()
    {
        return $this->getSubject()->getAddFromToWidget();
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->getSubject()->getIdentities();
    }

    /**
     * @return int
     */
    public function getIndexMode()
    {
        return $this->getSubject()->getIndexMode();
    }

    /**
     * @param string $currencySymbol
     * @return string
     */
    public function getUnitsLabel($currencySymbol = '')
    {
        return $this->getSubject()->getUnitsLabel($currencySymbol);
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        return $this->getSubject()->isMultiselect();
    }

    public function getSeoSignificant(): int
    {
        return $this->getSubject()->getSeoSignificant();
    }

    /**
     * @return bool
     */
    public function isExpanded()
    {
        return $this->getSubject()->isExpanded();
    }

    /**
     * @return int
     */
    public function getSortOptionsBy()
    {
        return $this->getSubject()->getSortOptionsBy();
    }

    /**
     * @return int
     */
    public function getShowProductQuantities()
    {
        return $this->getSubject()->getShowProductQuantities();
    }

    /**
     * @return bool
     */
    public function isShowSearchBox($optionsCount)
    {
        return $this->getSubject()->isShowSearchBox($optionsCount);
    }

    /**
     * @return mixed
     */
    public function getNumberUnfoldedOptions()
    {
        return $this->getSubject()->getNumberUnfoldedOptions();
    }

    /**
     * @return mixed
     */
    public function getTooltip()
    {
        return $this->getSubject()->getTooltip();
    }

    /**
     * @return string
     */
    public function getVisibleInCategories()
    {
        return $this->getSubject()->getVisibleInCategories();
    }

    /**
     * @return mixed
     */
    public function getCategoriesFilter()
    {
        return $this->getSubject()->getCategoriesFilter();
    }

    /**
     * @return mixed
     */
    public function getAttributesFilter()
    {
        return $this->getSubject()->getAttributesFilter();
    }

    /**
     * @return mixed
     */
    public function getAttributesOptionsFilter()
    {
        return $this->getSubject()->getAttributesOptionsFilter();
    }

    /**
     * @return bool
     */
    public function isUseAndLogic()
    {
        return $this->getSubject()->isUseAndLogic();
    }

    /**
     * @return int
     */
    public function getBlockPosition()
    {
        return $this->getSubject()->getBlockPosition();
    }

    /**
     * @return bool
     */
    public function getShowIconsOnProduct()
    {
        return $this->getSubject()->getShowIconsOnProduct();
    }

    /**
     * @return bool
     */
    public function getUnitsLabelUseCurrencySymbol()
    {
        return $this->getSubject()->getUnitsLabelUseCurrencySymbol();
    }

    /**
     * @param int $id
     * @return FilterSettingInterface
     */
    public function setId($id)
    {
        return $this->getSubject()->setId($id);
    }

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setDisplayMode($displayMode)
    {
        return $this->getSubject()->setDisplayMode($displayMode);
    }

    /**
     * @param int $displayMode
     * @return FilterSettingInterface
     */
    public function setCategoryTreeDisplayMode($displayMode)
    {
        return $this->getSubject()->setCategoryTreeDisplayMode($displayMode);
    }

    /**
     * @return int
     */
    public function getPositionLabel(): int
    {
        return $this->getSubject()->getPositionLabel();
    }

    /**
     * @param int $positionLabel
     */
    public function setPositionLabel(int $positionLabel): void
    {
        $this->getSubject()->setPositionLabel($positionLabel);
    }

    /**
     * @deprecated 2.16.0 use setAttributeCode
     * @param string $filterCode
     * @return FilterSettingInterface
     */
    public function setFilterCode($filterCode)
    {
        return $this->getSubject()->setFilterCode($filterCode);
    }

    /**
     * @param string $filterCode
     */
    public function setAttributeCode(string $filterCode): void
    {
        $this->getSubject()->setAttributeCode($filterCode);
    }

    /**
     * @param int $indexMode
     * @return FilterSettingInterface
     */
    public function setIndexMode($indexMode)
    {
        return $this->getSubject()->setIndexMode($indexMode);
    }

    /**
     * @param int $followMode
     * @return FilterSettingInterface
     */
    public function setFollowMode($followMode)
    {
        return $this->getSubject()->setFollowMode($followMode);
    }

    /**
     * @param int $relNofollow
     * @return FilterSettingInterface
     */
    public function setRelNofollow($relNofollow)
    {
        return $this->getSubject()->setRelNofollow($relNofollow);
    }

    /**
     * @param bool $isMultiselect
     * @return FilterSettingInterface
     */
    public function setIsMultiselect($isMultiselect)
    {
        return $this->getSubject()->setIsMultiselect($isMultiselect);
    }

    public function setSeoSignificant(int $seoSignificant): void
    {
        $this->getSubject()->setSeoSignificant($seoSignificant);
    }

    /**
     * @param bool $isExpanded
     * @return FilterSettingInterface
     */
    public function setIsExpanded($isExpanded)
    {
        return $this->getSubject()->setIsExpanded($isExpanded);
    }

    /**
     * @param bool $addFromToWidget
     *
     * @return FilterSettingInterface
     */
    public function setAddFromToWidget($addFromToWidget)
    {
        return $this->getSubject()->setAddFromToWidget($addFromToWidget);
    }

    /**
     * @param int $sortOptionsBy
     *
     * @return FilterSettingInterface
     */
    public function setSortOptionsBy($sortOptionsBy)
    {
        return $this->getSubject()->setSortOptionsBy($sortOptionsBy);
    }

    /**
     * @param int $showProductQuantities
     *
     * @return FilterSettingInterface
     */
    public function setShowProductQuantities($showProductQuantities)
    {
        return $this->getSubject()->setShowProductQuantities($showProductQuantities);
    }

    /**
     * @param int|null $isShowSearchBox
     */
    public function setIsShowSearchBox(?int $isShowSearchBox): void
    {
        $this->getSubject()->setIsShowSearchBox($isShowSearchBox);
    }

    /**
     * @param int $numberOfUnfoldedOptions
     *
     * @return FilterSettingInterface
     */
    public function setNumberUnfoldedOptions($numberOfUnfoldedOptions)
    {
        return $this->getSubject()->setNumberUnfoldedOptions($numberOfUnfoldedOptions);
    }

    /**
     * @param string $tooltip
     *
     * @return FilterSettingInterface
     */
    public function setTooltip($tooltip)
    {
        return $this->getSubject()->setTooltip($tooltip);
    }

    /**
     * @param string $visibleInCategories
     * @return FilterSettingInterface
     */
    public function setVisibleInCategories($visibleInCategories)
    {
        return $this->getSubject()->setVisibleInCategories($visibleInCategories);
    }

    /**
     * @param array $categoriesFilter
     * @return FilterSettingInterface
     */
    public function setCategoriesFilter($categoriesFilter)
    {
        return $this->getSubject()->setCategoriesFilter($categoriesFilter);
    }

    /**
     * @param array $attributesFilter
     * @return FilterSettingInterface
     */
    public function setAttributesFilter($attributesFilter)
    {
        return $this->getSubject()->setAttributesFilter($attributesFilter);
    }

    /**
     * @param array $attributesOptionsFilter
     * @return FilterSettingInterface
     */
    public function setAttributesOptionsFilter($attributesOptionsFilter)
    {
        return $this->getSubject()->setAttributesOptionsFilter($attributesOptionsFilter);
    }

    /**
     * @param bool $isUseAndLogic
     *
     * @return FilterSettingInterface
     */
    public function setIsUseAndLogic($isUseAndLogic)
    {
        return $this->getSubject()->setIsUseAndLogic($isUseAndLogic);
    }

    public function getSliderMin(): float
    {
        return $this->getSubject()->getSliderMin();
    }

    public function getSliderMax(): float
    {
        return $this->getSubject()->getSliderMax();
    }

    public function setSliderMin(float $value): void
    {
        $this->getSubject()->setSliderMin($value);
    }

    public function setSliderMax(float $value): void
    {
        $this->getSubject()->setSliderMax($value);
    }

    /**
     * @param int $blockPosition
     *
     * @return FilterSettingInterface
     */
    public function setBlockPosition($blockPosition)
    {
        return $this->getSubject()->setBlockPosition($blockPosition);
    }

    /**
     * @param bool $isShowLinks
     * @return FilterSettingInterface
     */
    public function setShowIconsOnProduct($isShowLinks)
    {
        return $this->getSubject()->setShowIconsOnProduct($isShowLinks);
    }

    public function setAttributeModel(?Attribute $attribute): void
    {
        $this->getSubject()->setAttributeModel($attribute);
    }

    public function getAttributeModel(): ?Attribute
    {
        return $this->getSubject()->getAttributeModel();
    }

    /**
     * @param string $label
     * @return void
     */
    public function setUnitsLabel(string $label): void
    {
        $this->getSubject()->setUnitsLabel($label);
    }

    /**
     * @param int $useCurrency
     * @return FilterSettingInterface
     */
    public function setUnitsLabelUseCurrencySymbol($useCurrency)
    {
        return $this->getSubject()->setUnitsLabelUseCurrencySymbol($useCurrency);
    }

    /**
     * @return int
     */
    public function getCategoryTreeDisplayMode()
    {
        return $this->getSubject()->getCategoryTreeDisplayMode();
    }

    /**
     * @return int|null
     */
    public function getLimitOptionsShowSearchBox(): ?int
    {
        return $this->getSubject()->getLimitOptionsShowSearchBox();
    }

    /**
     * @param int|null $limitOptionsShowSearchBox
     */
    public function setLimitOptionsShowSearchBox(?int $limitOptionsShowSearchBox): void
    {
        $this->getSubject()->setLimitOptionsShowSearchBox($limitOptionsShowSearchBox);
    }

    public function getTopPosition(): int
    {
        return $this->getSubject()->getTopPosition();
    }

    public function getSidePosition(): int
    {
        return $this->getSubject()->getSidePosition();
    }

    public function setTopPosition(int $topPosition): void
    {
        $this->getSubject()->setTopPosition($topPosition);
    }

    public function setSidePosition(int $sidePosition): void
    {
        $this->getSubject()->setSidePosition($sidePosition);
    }

    public function getPosition(): int
    {
        return $this->getSubject()->getPosition();
    }

    public function setPosition(int $position): void
    {
        $this->getSubject()->setPosition($position);
    }

    public function getCategoryTreeDepth(): ?int
    {
        return $this->getSubject()->getCategoryTreeDepth();
    }

    public function setCategoryTreeDepth(?int $treeDepth): void
    {
        $this->getSubject()->getCategoryTreeDepth();
    }

    public function getRenderCategoriesLevel(): ?int
    {
        return $this->getSubject()->getRenderCategoriesLevel();
    }

    public function setRenderCategoriesLevel(?int $level): void
    {
        $this->getSubject()->setRenderCategoriesLevel($level);
    }

    public function getRenderAllCategoriesTree(): ?bool
    {
        return $this->getSubject()->getRenderAllCategoriesTree();
    }

    public function setRenderAllCategoriesTree(?bool $renderAllCategoriesTree): void
    {
        $this->getSubject()->setRenderAllCategoriesTree($renderAllCategoriesTree);
    }

    public function getSubcategoriesView(): ?int
    {
        return $this->getSubject()->getSubcategoriesView();
    }

    public function setSubcategoriesView(?int $subcategoriesView): void
    {
        $this->getSubject()->setSubcategoriesView($subcategoriesView);
    }

    public function getSubcategoriesExpand(): ?int
    {
        return $this->getSubject()->getSubcategoriesExpand();
    }

    public function setSubcategoriesExpand(?int $subcategoriesExpand): void
    {
        $this->getSubject()->setSubcategoriesExpand($subcategoriesExpand);
    }

    public function getData($key = '', $index = null)
    {
        return $this->getSubject()->getData($key, $index);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setData($key, $value = null)
    {
        return $this->getSubject()->setData($key, $value);
    }

    public function addData($data): void
    {
        $this->getSubject()->addData($data);
    }

    /**
     * @deprecated
     * @return AbstractDb
     */
    public function getResource(): AbstractDb
    {
        return $this->getSubject()->getResource();
    }

    public function getIsShowSearchBox(): ?int
    {
        return $this->getSubject()->getIsShowSearchBox();
    }

    public function setAttributeUrlAlias(?string $alias): void
    {
        $this->getSubject()->setAttributeUrlAlias($alias);
    }

    public function getAttributeUrlAlias(): ?string
    {
        return $this->getSubject()->getAttributeUrlAlias();
    }

    public function setSliderStep(?float $step): void
    {
        $this->getSubject()->setSliderStep($step);
    }

    public function getSliderStep(): ?float
    {
        return $this->getSubject()->getSliderStep();
    }

    public function getHideZeros(): bool
    {
        return $this->getSubject()->getHideZeros();
    }

    public function setHideZeros(bool $hideZeros): void
    {
        $this->getSubject()->setHideZeros($hideZeros);
    }

    public function getRangeAlgorithm(): ?int
    {
        return $this->getSubject()->getRangeAlgorithm();
    }

    public function setRangeAlgorithm(int $rangeAlgorithm): void
    {
        $this->getSubject()->setRangeAlgorithm($rangeAlgorithm);
    }

    public function getMinRange(): ?int
    {
        return $this->getSubject()->getMinRange();
    }

    public function setMinRange(int $minRange): void
    {
        $this->getSubject()->setMinRange($minRange);
    }

    public function getMaxRange(): ?int
    {
        return $this->getSubject()->getMaxRange();
    }

    public function setMaxRange(int $maxRange): void
    {
        $this->getSubject()->setMaxRange($maxRange);
    }

    public function getRangeStep(): ?float
    {
        return $this->getSubject()->getRangeStep();
    }

    public function setRangeStep(float $rangeStep): void
    {
        $this->getSubject()->setRangeStep($rangeStep);
    }

    public function getAttributeId(): ?int
    {
        return $this->getSubject()->getAttributeId();
    }

    public function setAttributeId(int $attributeId): void
    {
        $this->getSubject()->setAttributeId($attributeId);
    }

    /**
     * Remove not serializable fields
     */
    public function __sleep(): array
    {
        $properties = array_keys(get_object_vars($this));

        return array_diff(
            $properties,
            [
                'filterSettingRepository',
            ]
        );
    }

    /**
     * Init not serializable fields
     */
    public function __wakeup(): void
    {
        $objectManager = ObjectManager::getInstance();
        $this->filterSettingRepository = $objectManager->get(FilterSettingRepositoryInterface::class);
    }
}
