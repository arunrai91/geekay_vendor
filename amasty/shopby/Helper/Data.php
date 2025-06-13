<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\Shopby\Model\Layer\Filter\Decimal as DecimalFilter;
use Amasty\Shopby\Model\Layer\Filter\Price as PriceFilter;
use Amasty\Shopby\Model\Layer\GetSelectedFiltersSettings;
use Amasty\Shopby\Model\Layer\IsBrandPage;
use Amasty\Shopby\Model\Request;
use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Amasty\Shopby;
use Magento\Framework\App\ObjectManager;
use Amasty\ShopbyBase\Helper\OptionSetting as OptionSettingHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data as SwatchesHelper;

class Data
{
    public const UNFOLDED_OPTIONS_STATE = 'amshopby/general/unfolded_options_state';

    public const CATALOG_SEO_SUFFIX_PATH = 'catalog/seo/category_url_suffix';
    public const AMSHOPBY_INDEX_INDEX = 'amshopby_index_index';
    public const SHOPBY_AJAX = 'shopbyAjax';

    /**
     * @var Layer|null
     */
    private ?Layer $layer = null;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Request
     */
    private Request $shopbyRequest;

    /**
     * @var SwatchesHelper
     */
    private SwatchesHelper $swatchHelper;

    /**
     * @var OptionSettingHelper
     */
    private OptionSettingHelper $optionSettingHelper;

    /**
     * @var UrlBuilderInterface
     */
    private UrlBuilderInterface $amUrlBuilder;

    /**
     * @var Resolver
     */
    private Resolver $layerResolver;

    public function __construct(
        Resolver $layerResolver,
        StoreManagerInterface $storeManager,
        Request $shopbyRequest,
        SwatchesHelper $swatchHelper,
        OptionSettingHelper $optionSettingHelper,
        UrlBuilderInterface $amUrlBuilder
    ) {
        $this->layerResolver = $layerResolver;
        $this->storeManager = $storeManager;
        $this->shopbyRequest = $shopbyRequest;
        $this->swatchHelper = $swatchHelper;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->amUrlBuilder = $amUrlBuilder;
    }

    /**
     * @return array
     * @deprecated
     * @see \Amasty\Shopby\Model\Layer\GetSelectedFiltersSettings::execute()
     */
    public function getSelectedFiltersSettings()
    {
        return ObjectManager::getInstance()->get(GetSelectedFiltersSettings::class)->execute();
    }

    /**
     * @param Shopby\Model\Layer\Filter\Item $filterItem
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFilterItemSelected(\Amasty\Shopby\Model\Layer\Filter\Item $filterItem)
    {
        $filter = $filterItem->getFilter();
        $data = $this->shopbyRequest->getFilterParam($filter);

        if (empty($data) && !is_numeric($data)) {
            return 0;
        }

        $ids = explode(',', $data);
        if ($this->isNeedCheckOption($filter)) {
            $ids = array_map('intval', $ids ?: []);
        }

        if (in_array($filterItem->getValue(), $ids)) {
            return 1;
        }

        return 0;
    }

    private function isNeedCheckOption(AbstractFilter $filter): bool
    {
        if ($filter instanceof DecimalFilter
            || $filter instanceof PriceFilter
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\Item[] $activeFilters
     * @return string
     */
    public function getAjaxCleanUrl($activeFilters)
    {
        $filterState = [];

        foreach ($activeFilters as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }

        $filterState['p'] = null;
        $filterState['dt'] = null;
        $filterState['df'] = null;

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;

        return str_replace('&amp;', '&', $this->amUrlBuilder->getUrl('*/*/*', $params));
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->getLayer()->getCurrentCategory();
    }

    /**
     * @return Layer
     */
    private function getLayer()
    {
        if (!$this->layer) {
            $this->layer = $this->layerResolver->get();
        }
        return $this->layer;
    }

    /**
     * @param array $optionIds
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return array
     */
    public function getSwatchesFromImages($optionIds, \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $swatches = [];
        if (!$this->swatchHelper->isVisualSwatch($attribute) && !$this->swatchHelper->isTextSwatch($attribute)) {
            /**
             * @TODO use collection method
             */
            foreach ($optionIds as $optionId) {
                $setting = $this->optionSettingHelper->getSettingByValue(
                    $optionId,
                    $attribute->getAttributeCode(),
                    $this->storeManager->getStore()->getId()
                );

                $swatches[$optionId] = [
                    'type' => 'option_image',
                    'value' => $setting->getSliderImageUrl()
                ];
            }
        }

        return $swatches;
    }

    /**
     * @return bool
     * @deprecated
     * @see \Amasty\Shopby\Model\Layer\IsBrandPage::execute
     */
    public function isBrandPage(): bool
    {
        return ObjectManager::getInstance()->get(IsBrandPage::class)->execute();
    }
}
