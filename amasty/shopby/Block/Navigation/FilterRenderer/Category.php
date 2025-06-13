<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation\FilterRenderer;

use Amasty\Shopby\Helper\Category as CategoryHelper;
use Amasty\Shopby\Model\Config\MobileConfigResolver;
use Amasty\Shopby\Model\Layer\Filter\Item as FilterItem;
use Amasty\Shopby\Model\UrlResolver\UrlResolverInterface;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\Shopby\Model\Source\SubcategoriesExpand;
use Amasty\Shopby\Model\Source\SubcategoriesView;
use Amasty\ShopbyBase\Model\FilterSetting\IsApplyFlyOut;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Amasty\ShopbyBase\Model\FilterSetting\IsShowProductQuantities;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class Category extends \Magento\Framework\View\Element\Template
{
    public const DEFAULT_LEVEL = 1;

    public const TEMPLATE_STORAGE_PATH = 'layer/filter/category/items/renderer/labels.phtml';

    /**
     * @var  FilterSetting
     */
    protected $settingHelper;

    /**
     * @var ShopbyHelper
     */
    protected $helper;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /**
     * @var UrlResolverInterface
     */
    private $urlResolver;

    /**
     * @var IsApplyFlyOut
     */
    private $isApplyFlyOut;

    /**
     * @var IsShowProductQuantities
     */
    private $isShowProductQuantities;

    /**
     * @var IsMultiselect
     */
    private $isMultiselect;

    /**
     * @var MobileConfigResolver
     */
    private $mobileConfigResolver;

    public function __construct(
        Context $context,
        FilterSetting $settingHelper,
        ShopbyHelper $helper,
        Resolver $resolver,
        CategoryHelper $categoryHelper,
        UrlResolverInterface $urlResolver,
        IsApplyFlyOut $isApplyFlyOut,
        IsShowProductQuantities $isShowProductQuantities,
        IsMultiselect $isMultiselect,
        MobileConfigResolver $mobileConfigResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->helper = $helper;
        $this->layer = $resolver->get();
        $this->categoryHelper = $categoryHelper;
        $this->urlResolver = $urlResolver;
        $this->isApplyFlyOut = $isApplyFlyOut;
        $this->isShowProductQuantities = $isShowProductQuantities;
        $this->isMultiselect = $isMultiselect;
        $this->mobileConfigResolver = $mobileConfigResolver;
    }

    /**
     * @param string $path = null
     * @return string
     */
    public function render($path = null)
    {
        $this->setPath($path);
        $this->setTemplate(self::TEMPLATE_STORAGE_PATH);

        return $this->toHtml();
    }

    /**
     * Render all children for current category path
     *
     * @param string $path
     * @return string
     */
    public function renderChildrenItems($path)
    {
        return $this->getLayout()
            ->createBlock(self::class)
            ->setFilter($this->getFilter())
            ->setLevel($this->getLevel() + self::DEFAULT_LEVEL)
            ->render($path);
    }

    /**
     * @param FilterItem $filterItem
     * @return int
     */
    public function checkedFilter(FilterItem $filterItem)
    {
        return $this->helper->isFilterItemSelected($filterItem)
            || $filterItem->getValue() == $this->layer->getCurrentCategory()->getId();
    }

    /**
     * Retrieve active filters
     *
     * @return string
     */
    public function collectFilters()
    {
        return $this->mobileConfigResolver->getSubmitFilterMode();
    }

    /**
     * @return string
     */
    public function getClearUrl(): string
    {
        return $this->urlResolver->resolve();
    }

    /**
     * @return \Amasty\Shopby\Model\Layer\Filter\Category
     * @throws LocalizedException
     */
    public function getFilter()
    {
        if (!$this->getData('filter') instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            throw new LocalizedException(__('Wrong Filter Type'));
        }

        return $this->getData('filter');
    }

    /**
     * @param int $categoryId
     * @return bool
     * @throws LocalizedException
     */
    public function isShowThumbnail($categoryId)
    {
        return $this->getFilter()->useImagesOnly() || $this->getCategoryHelper()->isCategoryImageExist($categoryId);
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->getData('level') ?: self::DEFAULT_LEVEL;
    }

    /**
     * @param string $data
     * @return string
     */
    public function escapeId($data)
    {
        return str_replace(",", "_", $data);
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        $filterSetting = $this->getFilterSetting();
        $isMultiselect = $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );

        return $isMultiselect ? 'checkbox' : 'radio';
    }

    /**
     * Retrieve setting for category layer filter
     *
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting()
    {
        if (!$this->getData('filter_setting')) {
            $setting = $this->settingHelper->getSettingByLayerFilter($this->getFilter());
            $this->setData('filter_setting', $setting);
        }

        return $this->getData('filter_setting');
    }

    /**
     * @param null $currentPath
     * @return bool
     */
    public function isExpandByClick($currentPath = null)
    {
        return $this->getChildren($currentPath)
            && $this->getFilterSetting()->getSubcategoriesExpand() == SubcategoriesExpand::BY_CLICK
            && $this->getFilterSetting()->getSubcategoriesView() == SubcategoriesView::FOLDING;
    }

    /**
     * @param $currentPath
     * @return int
     */
    public function getChildren($currentPath)
    {
        return $this->getFilter()->getItems()->getItemsCount($currentPath);
    }

    /**
     * @param \Amasty\Shopby\Model\Layer\Filter\CategoryItems $filterItems
     * @param string|null $path
     * @return bool
     */
    public function isParent($filterItems, $path)
    {
        foreach ($filterItems->getItems($path) as $filterItem) {
            if ($filterItem->getData('count') > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return CategoryHelper
     */
    public function getCategoryHelper()
    {
        return $this->categoryHelper;
    }

    /**
     * @return bool
     */
    public function isFolding()
    {
        return !$this->isApplyFlyOut->execute((int) $this->getFilterSetting()->getSubcategoriesView());
    }

    public function isShowProductQuantities(?int $showProductQuantities): bool
    {
        return $this->isShowProductQuantities->execute($showProductQuantities);
    }

    public function isFilterActive(FilterItem $filterItem): bool
    {
        return in_array($filterItem->getValue(), $this->layer->getCurrentCategory()->getPathIds(), false);
    }
}
