<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    public const AMSHOPBY_SEO_REL_NOFOLLOW = 'robots/rel_nofollow';

    public const GROUP_URL = 'url/';

    public const GROUP_CANONICAL = 'canonical/';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_shopby_seo/';

    public function isEnableRelNofollow(): bool
    {
        return (bool) $this->getValue(self::AMSHOPBY_SEO_REL_NOFOLLOW);
    }

    /*
     * URL group.
     */

    /**
     * @param int|ScopeInterface|null $storeId
     */
    public function isSeoUrlEnabled($storeId = null): bool
    {
        return $this->isSetFlag(self::GROUP_URL . 'mode', $storeId);
    }

    public function isGenerateSeoByDefault(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::GROUP_URL . 'is_generate_seo_default', $storeId);
    }

    public function isIncludeAttributeName(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::GROUP_URL . 'attribute_name', $storeId);
    }

    public function getOptionSeparator(): string
    {
        return (string) $this->getValue(self::GROUP_URL . 'option_separator');
    }

    /**
     * Returns symbol to replace special chars.
     */
    public function getSpecialChar(?int $storeId = null): string
    {
        return (string) $this->getValue(self::GROUP_URL . 'special_char', $storeId);
    }

    /*
     * Canonical group.
     * Canonical URL settings for different page types.
     */

    public function getCanonicalRoot(?int $storeId = null): string
    {
        return (string) $this->getValue(self::GROUP_CANONICAL . 'root', $storeId);
    }

    public function getCanonicalBrand(?int $storeId = null): string
    {
        return (string) $this->getValue(self::GROUP_CANONICAL . 'brand', $storeId);
    }

    public function getCanonicalCategory(?int $storeId = null): string
    {
        return (string) $this->getValue(self::GROUP_CANONICAL . 'category', $storeId);
    }

    public function isAddSuffix(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::GROUP_URL . 'add_suffix_shopby', $storeId);
    }

    public function getSeoSuffix(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFilterWord(?int $storeId = null): string
    {
        return (string) $this->getValue(self::GROUP_URL . 'filter_word', $storeId);
    }

    public function isNeedAddCanonicalForNoindexPages(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::GROUP_CANONICAL . 'add_canonical_for_noindex', $storeId);
    }

    /**
     * @return string[]
     */
    public function getIgnoredUrls(): array
    {
        return $this->convertStringToArray(
            (string)$this->getValue(self::GROUP_URL . 'ignored_urls')
        );
    }

    /**
     * @return string[]
     */
    private function convertStringToArray(string $value, string $separator = PHP_EOL): array
    {
        if (empty($value)) {
            return [];
        }

        return array_filter(array_map(
            'trim',
            explode($separator, $value)
        ));
    }
}
