<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model;

use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const TOOLTIPS_ENABLED = 'tooltips/enabled';
    public const TOOLTIP_IMAGE = 'tooltips/image';
    public const KEEP_SINGLE_CHOICE_VISIBLE = 'general/keep_single_choice_visible';
    public const HIDE_FILTERS_WITH_ONE_OPTION = 'general/hide_filters_with_one_option';
    public const STOCK_SOURCE = 'stock_filter/stock_source';
    public const STOCK_FILTER_ENABLED = 'stock_filter/enabled';
    public const RATING_FILTER_ENABLED = 'rating_filter/enabled';
    public const IS_NEW_FILTER_ENABLED = 'am_is_new_filter/enabled';
    public const ON_SALE_FILTER_ENABLED = 'am_on_sale_filter/enabled';
    public const EXCLUDE_OUT_OF_STOCK = 'general/exclude_out_of_stock';
    public const BRAND_ATTRIBUTE_CODE = 'amshopby_brand/general/attribute_code';
    public const ENABLE_OVERFLOW_SCROLL = 'general/enable_overflow_scroll';
    public const ENABLE_STICKY_SIDEBAR_DESKTOP = 'general/enable_sticky_sidebar_desktop';
    public const SLIDER_STYLE = 'slider/slider_style';
    public const SLIDER_COLOR = 'slider/slider_color';
    public const CATEGORY_FILTER_POSITION = 'category_filter/position';
    public const AJAX_ENABLED = 'general/ajax_enabled';
    public const UNFOLDED_OPTIONS_STATE = 'general/unfolded_options_state';
    public const CHILDREN_CATEGORIES_INFINITY_LOOP = 'children_categories/infinity_loop';

    /**
     * @var string
     */
    protected $pathPrefix = 'amshopby/';

    public function isTooltipsEnabled(): string
    {
        return (string)$this->getValue(self::TOOLTIPS_ENABLED);
    }

    public function isEnableOverflowScroll(): bool
    {
        return (bool) $this->getValue(self::ENABLE_OVERFLOW_SCROLL);
    }

    public function getOverflowScrollValue(): int
    {
        return (int) $this->getValue(self::ENABLE_OVERFLOW_SCROLL);
    }

    public function isEnableStickySidebarDesktop(): bool
    {
        return (bool) $this->getValue(self::ENABLE_STICKY_SIDEBAR_DESKTOP);
    }

    public function getTooltipSrc(): string
    {
        return (string)$this->getValue(self::TOOLTIP_IMAGE);
    }

    public function isSingleChoiceMode(): bool
    {
        return $this->isSetFlag(self::KEEP_SINGLE_CHOICE_VISIBLE);
    }

    public function isStockFilterEnabled(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::STOCK_FILTER_ENABLED, $storeId);
    }

    public function isRatingFilterEnabled(): bool
    {
        return $this->isSetFlag(self::RATING_FILTER_ENABLED);
    }

    public function isNewFilterEnabled(): bool
    {
        return $this->isSetFlag(self::IS_NEW_FILTER_ENABLED);
    }

    public function isSaleFilterEnabled(): bool
    {
        return $this->isSetFlag(self::ON_SALE_FILTER_ENABLED);
    }

    public function getBrandAttributeCode(): string
    {
        return (string) $this->getGlobalValue(self::BRAND_ATTRIBUTE_CODE);
    }

    public function isExcludeOutOfStock(): bool
    {
        return (bool)$this->getValue(self::EXCLUDE_OUT_OF_STOCK);
    }

    public function getStockConfig(): array
    {
        return $this->getValue('stock_filter');
    }

    public function isStockByReservedQty(?int $storeId = null): bool
    {
        return $this->isSetFlag('stock_filter/use_salable_qty', $storeId);
    }

    public function getRatingConfig(): array
    {
        return $this->getValue('rating_filter');
    }

    public function getNewConfig(): array
    {
        return $this->getValue('am_is_new_filter');
    }

    public function getOnSaleConfig(): array
    {
        return $this->getValue('am_on_sale_filter');
    }

    public function getSliderStyle(?int $storeId = null, ?string $scope = ScopeInterface::SCOPE_STORE): string
    {
        return (string) $this->getValue(self::SLIDER_STYLE, $storeId, $scope);
    }

    public function getSliderColor(?int $storeId = null, ?string $scope = ScopeInterface::SCOPE_STORE): string
    {
        return (string) $this->getValue(self::SLIDER_COLOR, $storeId, $scope);
    }

    public function getCategoryPosition(?int $storeId = null): int
    {
        return (int) $this->getValue(self::CATEGORY_FILTER_POSITION, $storeId);
    }

    public function isAjaxEnabled(): bool
    {
        return $this->isSetFlag(self::AJAX_ENABLED);
    }

    public function isHideFilterWithOneOption(): bool
    {
        return $this->isSetFlag(self::HIDE_FILTERS_WITH_ONE_OPTION);
    }

    /**
     * @see \Amasty\Shopby\Model\Source\SubmitMode
     */
    public function getSubmitFiltersDesktop(): int
    {
        return (int)$this->getValue('general/submit_filters_on_desktop');
    }

    /**
     * @see \Amasty\Shopby\Model\Source\SubmitMode
     */
    public function getSubmitFiltersMobile(): int
    {
        return (int) $this->getValue('general/submit_filters_on_mobile');
    }

    public function getCatalogManageStock(): int
    {
        return (int)$this->scopeConfig->getValue(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK);
    }

    public function getCatalogMinQty(): float
    {
        return (float)$this->scopeConfig->getValue(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MIN_QTY);
    }

    public function getUnfoldedCount(): int
    {
        return (int)$this->getValue(self::UNFOLDED_OPTIONS_STATE);
    }

    public function isCategoryInfinityLoop(): bool
    {
        return $this->isSetFlag(self::CHILDREN_CATEGORIES_INFINITY_LOOP);
    }
}
