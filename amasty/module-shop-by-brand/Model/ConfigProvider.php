<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    public const DEFAULT_CATEGORY_LOGO_SIZE = 30;

    public const CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';
    public const SHOPBY_SEO_SUFFIX = 'amasty_shopby_seo/url/add_suffix_shopby';

    /**
     * General group settings path
     */
    public const BRAND_ATTRIBUTE_CODE = 'general/attribute_code';
    public const BRAND_URL_KEY = 'general/url_key';
    public const TOOLTIP_ENABLED = 'general/tooltip_enabled';
    public const EXCLUDE_EMPTY_SITEMAP_BRAND = 'general/exclude_empty_sitemap_brand';

    /**
     * Product Page group settings path
     */
    public const DISPLAY_DESCRIPTION = 'product_page/display_description';
    public const PRODUCT_WIDTH = 'product_page/width';
    public const LOGO_HEIGHT = 'product_page/height';
    public const DISPLAY_BRAND_IMAGE = 'product_page/display_brand_image';
    private const DISPLAY_TITLE = 'product_page/display_title';

    /**
     * Product Listing group settings path
     */
    public const SHOW_ON_LISTING = 'product_listing_settings/show_on_listing';
    public const LISTING_BRAND_LOGO_WIDTH = 'product_listing_settings/listing_brand_logo_width';
    public const LISTING_BRAND_LOGO_HEIGHT = 'product_listing_settings/listing_brand_logo_height';

    /**
     * More From Brand group settings path
     */
    public const MORE_FROM_ENABLE = 'more_from_brand/enable';
    public const MORE_FROM_TITLE = 'more_from_brand/title';
    public const MORE_FROM_COUNT = 'more_from_brand/count';

    /**
     * @var string
     */
    protected $pathPrefix = 'amshopby_brand/';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $allBrandAttributeCodes;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($scopeConfig);
        $this->storeManager = $storeManager;
    }

    public function getAllBrandAttributeCodes(): array
    {
        if ($this->allBrandAttributeCodes === null) {
            $attributes = [];
            foreach ($this->storeManager->getStores() as $store) {
                $code = $this->getBrandAttributeCode((int) $store->getId());
                if ($code) {
                    $attributes[$store->getId()] = $code;
                }
            }

            $this->allBrandAttributeCodes = array_unique($attributes);
        }

        return $this->allBrandAttributeCodes;
    }

    /*
     * General group settings
     */

    public function getBrandAttributeCode(?int $storeId = null): string
    {
        return (string) $this->getValue(self::BRAND_ATTRIBUTE_CODE, $storeId);
    }

    /**
     * @return string[]
     * @see \Amasty\ShopbyBrand\Model\Source\Tooltip
     */
    public function getTooltipEnabled(?int $storeId = null): array
    {
        return explode(',', (string) $this->getValue(self::TOOLTIP_ENABLED, $storeId));
    }

    public function isExcludeEmptySitemapBrand(?int $storeId): bool
    {
        return $this->isSetFlag(self::EXCLUDE_EMPTY_SITEMAP_BRAND, $storeId);
    }

    /*
     * Product Listing group settings path
     */

    public function isShowOnListing(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::SHOW_ON_LISTING, $storeId);
    }

    public function getListingBrandLogoWidth(?int $storeId = null): int
    {
        return (int) $this->getValue(self::LISTING_BRAND_LOGO_WIDTH, $storeId) ?: self::DEFAULT_CATEGORY_LOGO_SIZE;
    }

    public function getListingBrandLogoHeight(?int $storeId = null): int
    {
        return (int) $this->getValue(self::LISTING_BRAND_LOGO_HEIGHT, $storeId) ?: self::DEFAULT_CATEGORY_LOGO_SIZE;
    }

    /*
     * Product Page group settings
     */

    public function isDisplayBrandImage(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::DISPLAY_BRAND_IMAGE, $storeId);
    }

    public function isDisplayDescription(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::DISPLAY_DESCRIPTION, $storeId);
    }

    /**
     * Brand Logo Width for product.
     */
    public function getLogoWidth(?int $storeId = null): int
    {
        return (int) $this->getValue(self::PRODUCT_WIDTH, $storeId);
    }

    /**
     * Brand Logo Height for product.
     */
    public function getLogoHeight(?int $storeId = null): int
    {
        return (int) $this->getValue(self::LOGO_HEIGHT, $storeId);
    }

    /*
     * More From Brand group settings path
     */

    public function isMoreFromEnabled(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::MORE_FROM_ENABLE, $storeId);
    }

    public function getTitleMoreFrom(?int $storeId = null): string
    {
        return (string) $this->getValue(self::MORE_FROM_TITLE, $storeId);
    }

    public function getMoreFromProductsLimit(?int $storeId = null): int
    {
        return (int) $this->getValue(self::MORE_FROM_COUNT, $storeId);
    }

    public function isDisplayTitle(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::DISPLAY_TITLE, $storeId);
    }

    public function getSuffix(): string
    {
        $suffix = '';
        if ($this->scopeConfig->isSetFlag(self::SHOPBY_SEO_SUFFIX)) {
            $suffix = (string)$this->scopeConfig
                ->getValue(self::CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE);
        }

        return $suffix;
    }

    public function getBrandUrlKey(?int $storeId = null): ?string
    {
        $urlKeyValue = $this->getValue(self::BRAND_URL_KEY, $storeId);
        return $urlKeyValue ?: null;
    }
}
