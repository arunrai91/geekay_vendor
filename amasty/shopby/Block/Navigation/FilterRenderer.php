<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Helper\Category;
use Amasty\Shopby\Model\Config\MobileConfigResolver;
use Amasty\Shopby\Model\Source\SubcategoriesView;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\Shopby\Helper\UrlBuilder;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Layer\Filter\Item;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Model\FilterSetting\IsApplyFlyOut;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Amasty\ShopbyBase\Model\FilterSetting\IsShowProductQuantities;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer implements RendererInterface
{
    public const TOP_NAV_RENDERER_NAME = 'amshopby.catalog.topnav.renderer';

    /**
     * @var  FilterSetting
     */
    protected $settingHelper;

    /**
     * @var  UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var  FilterInterface
     */
    protected $filter;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var ShopbyHelper
     */
    protected $helper;

    /**
     * @var Category
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /**
     * @var IsApplyFlyOut
     */
    private $isApplyFlyOut;

    /**
     * @var IsShowProductQuantities
     */
    private $isShowProductQuantities;

    /**
     * @var ConfigProvider|null
     */
    private $configProvider;

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
        UrlBuilder $urlBuilder,
        ShopbyHelper $helper,
        Category $categoryHelper,
        Resolver $resolver,
        IsApplyFlyOut $isApplyFlyOut,
        IsShowProductQuantities $isShowProductQuantities,
        ConfigProvider $configProvider,
        IsMultiselect $isMultiselect,
        MobileConfigResolver $mobileConfigResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->categoryHelper = $categoryHelper;
        $this->layer = $resolver->get();
        $this->isApplyFlyOut = $isApplyFlyOut;
        $this->isShowProductQuantities = $isShowProductQuantities;
        $this->configProvider = $configProvider;
        $this->isMultiselect = $isMultiselect;
        $this->mobileConfigResolver = $mobileConfigResolver;
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $this->filter = $filter;
        $setting = $this->settingHelper->getSettingByLayerFilter($filter);

        if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            $categoryTreeHtml = $this->getCategoryTreeHtml($filter);
            $this->assign('categoryTreeHtml', $categoryTreeHtml);
            $template = $this->getCustomTemplateForCategoryFilter($setting);
        } else {
            $template = $this->getTemplateByFilterSetting($setting);
        }

        $this->setTemplate($template);
        $this->assign('filterSetting', $setting);

        if ($this->filter instanceof \Amasty\Shopby\Api\Data\FromToFilterInterface) {
            $fromToConfig = $this->filter->getFromToConfig();
            $this->assign('fromToConfig', $fromToConfig);
        }

        $html = parent::render($filter)
            . $this->getTooltipHtml($setting)
            . $this->getShowMoreHtml($setting);
        return $html;
    }

    public function getEnableOverflowScroll(FilterSettingInterface $filterSetting): bool
    {
        return !($filterSetting->getSubcategoriesView() == SubcategoriesView::FLY_OUT
                || $filterSetting->getSubcategoriesView() == SubcategoriesView::FLY_OUT_FOR_DESKTOP_ONLY)
            && $this->configProvider->isEnableOverflowScroll();
    }

    public function getOverflowScrollValue(): int
    {
        return $this->configProvider->getOverflowScrollValue();
    }

    /**
     * @param FilterSettingInterface $setting
     *
     * @return string
     */
    protected function getShowMoreHtml(FilterSettingInterface $setting)
    {
        return $this->settingHelper->getShowMoreButtonBlock($setting)->toHtml();
    }

    /**
     * @param FilterInterface $filter
     *
     * @return string
     */
    protected function getCategoryTreeHtml(FilterInterface $filter)
    {
        return $this->getLayout()
            ->createBlock(\Amasty\Shopby\Block\Navigation\FilterRenderer\Category::class)
            ->setFilter($filter)
            ->render();
    }

    public function getTooltipHtml(FilterSettingInterface $setting): string
    {
        if (!$this->isShowTooltip($setting->getTooltip())) {
            return '';
        }

        return $this->getLayout()->createBlock(\Amasty\Shopby\Block\Navigation\Widget\Tooltip::class)
            ->setFilterSetting($setting)
            ->toHtml();
    }

    private function isShowTooltip(?string $tooltip): bool
    {
        return $this->configProvider && $this->configProvider->isTooltipsEnabled() && !empty($tooltip);
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return string
     */
    protected function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_SLIDER:
                $template = "layer/filter/slider.phtml";
                break;
            case DisplayMode::MODE_FROM_TO_ONLY:
                $template = "layer/widget/fromto.phtml";
                break;
            default:
                $template = "layer/filter/default.phtml";
                break;
        }
        return $template;
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return string
     */
    protected function getCustomTemplateForCategoryFilter(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_DROPDOWN:
                $template = "layer/filter/category/dropdown.phtml";
                break;
            default:
                if ($this->isApplyFlyOut->execute((int) $filterSetting->getSubcategoriesView())) {
                    $template = 'layer/filter/category/labels_fly_out.phtml';
                } else {
                    $template = 'layer/filter/category/labels_folding.phtml';
                }
                break;
        }
        return $template;
    }

    /**
     * @param \Amasty\Shopby\Model\Layer\Filter\Item $filterItem
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkedFilter(\Amasty\Shopby\Model\Layer\Filter\Item $filterItem)
    {
        $checked = $this->helper->isFilterItemSelected($filterItem);

        if (!$checked && $filterItem->getFilter()->getRequestVar() == 'cat') {
            $checked = $filterItem->getValue() == $this->layer->getCurrentCategory()->getId();
        }
        return $checked;
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        if (isset($this->_viewVars['filterItems']) && is_array($this->_viewVars['filterItems'])) {
            foreach ($this->_viewVars['filterItems'] as $item) {
                /** @var Item $item */
                if ($this->checkedFilter($item)) {
                    return $item->getRemoveUrl();
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSliderUrlTemplate()
    {
        return $this->urlBuilder->buildUrl($this->filter, 'amshopby_slider_from-amshopby_slider_to');
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
    public function collectFilters()
    {
        return $this->mobileConfigResolver->getSubmitFilterMode();
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    public function getRadioAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'amshopby/general/keep_single_choice_visible',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return Category
     */
    public function getCategoryHelper()
    {
        return $this->categoryHelper;
    }

    /**
     * @return bool
     */
    public function isTopNav()
    {
        return $this->getNameInLayout() == self::TOP_NAV_RENDERER_NAME;
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting()
    {
        $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this->filter);

        return $this->filterSetting;
    }

    /**
     * @return string
     */
    public function getFromToWidget($type)
    {
        return $this->getLayout()->createBlock(
            \Amasty\Shopby\Block\Navigation\Widget\FromTo::class
        )
            ->assign('filterSetting', $this->getFilterSetting())
            ->assign('fromToConfig', $this->filter->getFromToConfig())
            ->setSliderUrlTemplate($this->getSliderUrlTemplate())
            ->setFilter($this->filter)
            ->setWidgetType($type)
            ->toHtml();
    }

    /**
     * @return string
     */
    public function getSearchForm()
    {
        return $this->getLayout()->createBlock(
            \Amasty\Shopby\Block\Navigation\Widget\SearchForm::class
        )
            ->assign('filterCode', $this->getFilterSetting()->getAttributeCode())
            ->setFilter($this->filter)
            ->toHtml();
    }

    /**
     * @return int
     */
    public function getCurrentCategoryId()
    {
        return $this->helper->getCurrentCategory()->getId();
    }

    public function getSliderStyle(): string
    {
        return $this->configProvider->getSliderStyle();
    }

    public function getSliderColor(): string
    {
        return $this->configProvider->getSliderColor();
    }

    public function isShowProductQuantities(?int $showProductQuantities): bool
    {
        return $this->isShowProductQuantities->execute($showProductQuantities);
    }

    public function isMultiselect(FilterSettingInterface $filterSetting): bool
    {
        return $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );
    }
}
