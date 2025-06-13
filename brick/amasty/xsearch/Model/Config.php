<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\Xsearch\Model\System\Config\Source\RelatedTerms;
use Amasty\Xsearch\Model\System\Config\Source\CategoryOptions;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Config extends ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_xsearch/';

    private const PERMANENT_REDIRECT_CODE = 301;
    private const TEMPORARY_REDIRECT_CODE = 302;
    private const DEFAULT_POPUP_WIDTH = 900;

    private const XML_PATH_FULL_SCREEN = 'general/full_screen';
    private const XML_PATH_TEMPLATE_WIDTH = 'general/popup_width';
    private const XML_PATH_TEMPLATE_MIN_CHARS = 'general/min_chars';
    private const XML_PATH_TEMPLATE_DELAY = 'general/delay';
    private const XML_PATH_TEMPLATE_DISPLAY_SEARCH_BUTTON = 'general/display_search_button';
    private const XML_PATH_TEMPLATE_DYNAMIC_WIDTH = 'general/dynamic_search_width';
    private const XML_PATH_IS_SAVE_SEARCH_INPUT_VALUE_ENABLED = 'general/enable_save_search_input_value';
    private const XML_PATH_USER_SEARCH_AUTOCLEANING = 'general/user_search_autocleaning';
    private const XML_PATH_USER_SEARCH_CLEANING_PERIOD = 'general/user_search_cleaning_period';
    public const XML_PATH_RECENT_SEARCHES_FIRST_CLICK = 'recent_searches/first_click';
    public const XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED = 'recent_searches/enabled';
    public const XML_PATH_POPULAR_SEARCHES_FIRST_CLICK = 'popular_searches/first_click';
    public const XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED = 'popular_searches/enabled';
    public const XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION = 'recent_searches/position';
    public const XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION = 'popular_searches/position';
    public const XML_PATH_BROWSING_HISTORY_FIRST_CLICK = 'browsing_history/first_click';
    public const XML_PATH_BROWSING_HISTORY_ENABLED = 'browsing_history/enabled';
    public const XML_PATH_BROWSING_HISTORY_POSITION = 'browsing_history/position';
    private const XML_PATH_EXCLUDED_CMS_PAGES = 'page/excluded_pages';
    public const XML_PATH_TEMPLATE_RECENT_VIEWED_ENABLED = 'recently_viewed/enabled';
    private const XML_PATH_TEMPLATE_RECENT_VIEWED_TITLE = 'recently_viewed/title';
    private const XML_PATH_TEMPLATE_RECENT_VIEWED_LIMIT = 'recently_viewed/limit';
    private const XML_PATH_TEMPLATE_IS_REDIRECT_TO_CART = 'checkout/cart/redirect_to_cart';
    private const XML_PATH_EMPTY_RESULT_BLOCK = 'general/empty_result_block';
    public const XML_PATH_BESTSELLERS_ENABLED = 'bestsellers/enabled';
    private const XML_PATH_BESTSELLERS_TITLE = 'bestsellers/title';
    private const XML_PATH_BESTSELLERS_POSITION = 'bestsellers/position';
    private const XML_PATH_BESTSELLERS_LIMIT = 'bestsellers/limit';
    private const XML_PATH_ENABLE_POPUP_INDEX = 'general/enable_popup_index';
    private const XML_PATH_TEMPLATE_OUT_OF_STOCK_LAST = 'product/out_of_stock_last';
    private const XML_PATH_AMASTY_ELASTIC_CONNECTION = 'amasty_elastic/connection/';
    private const XML_PATH_CATALOG_SEARCH = 'catalog/search/';
    private const XML_PATH_TEMPLATE_PRODUCT_ENABLED = 'product/enabled';
    private const XML_PATH_TEMPLATE_PRODUCT_LIMIT = 'product/limit';
    private const XML_PATH_TEMPLATE_ENABLE_RELEVANCE_RULES_IN_POPUP = 'product/apply_relevance_rules_in_popup';
    private const XML_PATH_DISPLAY_FULL_CATEGORY_PATH = 'category/full_path';
    private const XML_PATH_POPUP_VIEW_TYPE = 'product/popup_display';
    private const XML_PATH_SHOW_SKU = 'product/show_sku';
    public const XML_PATH_IS_SEO_URL_ENABLED = 'general/enable_seo_url';
    public const XML_PATH_SEO_KEY = 'general/seo_key';
    public const XML_PATH_TEMPLATE_CATEGORY_EXCLUDE = 'category/exclude_ids';

    private const XML_PATH_LAYOUT_ENABLED = 'layout/enabled';
    private const XML_PATH_LAYOUT_BORDER = 'layout/border';
    private const XML_PATH_LAYOUT_HOVER = 'layout/hover';
    private const XML_PATH_LAYOUT_HIGHLIGHT = 'layout/highlight';
    private const XML_PATH_LAYOUT_BACKGROUND = 'layout/background';
    private const XML_PATH_LAYOUT_TEXT = 'layout/text';
    private const XML_PATH_LAYOUT_HOVER_TEXT = 'layout/hover_text';
    private const XML_PATH_LAYOUT_SEARCH_BUTTON = 'layout/search_button';
    private const XML_PATH_LAYOUT_SEARCH_BUTTON_TEXT = 'layout/search_button_text';

    /**
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->getValue($path, $storeId);
    }

    public function getGeneralConfig(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFlagConfig(string $path, $storeId = null): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShowProductReviews(): bool
    {
        return $this->getFlagConfig(PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE);
    }

    /**
     * @return int
     */
    public function getRedirectType()
    {
        return $this->getModuleConfig('general/four_zero_four_redirect');
    }

    public function isUserSearchAutocleaningEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_USER_SEARCH_AUTOCLEANING);
    }

    public function getUserSearchCleaningPeriod(): int
    {
        return (int)($this->getModuleConfig(self::XML_PATH_USER_SEARCH_CLEANING_PERIOD) ?: 365);
    }

    /**
     * @return bool
     */
    public function hasRedirect()
    {
        return (bool)$this->getRedirectType();
    }

    /**
     * @return bool
     */
    public function isPermanentRedirect()
    {
        return $this->getRedirectType() == self::PERMANENT_REDIRECT_CODE;
    }

    /**
     * @return int
     */
    public function getRedirectCode()
    {
        return $this->isPermanentRedirect() ? self::PERMANENT_REDIRECT_CODE : self::TEMPORARY_REDIRECT_CODE;
    }

    /**
     * @param int $searchResultCount
     * @return bool
     */
    public function canShowRelatedTerms($searchResultCount = 0)
    {
        switch ($this->getModuleConfig('general/show_related_terms')) {
            case RelatedTerms::DISABLED:
                return false;
            case RelatedTerms::SHOW_ALWAYS:
                return true;
            case RelatedTerms::SHOW_ONLY_WITHOUT_RESULTS:
                return !$searchResultCount;
        }

        return false;
    }
    /**
     * @return bool
     */
    public function canShowRelatedNumberResults()
    {
        return (bool)$this->getModuleConfig('general/show_related_terms_results');
    }

    public function isShowOutOfStockLast(): bool
    {
        return (bool) $this->getModuleConfig(self::XML_PATH_TEMPLATE_OUT_OF_STOCK_LAST);
    }

    public function getShowRecentByFirstClick(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_RECENT_SEARCHES_FIRST_CLICK);
    }

    public function getShowPopularByFirstClick(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_POPULAR_SEARCHES_FIRST_CLICK);
    }

    public function getBrowsingHistoryByFirstClick(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_BROWSING_HISTORY_FIRST_CLICK);
    }

    public function isDynamicWidth(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_DYNAMIC_WIDTH);
    }

    public function getPopupWidth(): int
    {
        return (int)$this->getModuleConfig(self::XML_PATH_TEMPLATE_WIDTH) ?: self::DEFAULT_POPUP_WIDTH;
    }

    public function isDisplaySearchButton(): bool
    {
        return (bool) $this->getModuleConfig(self::XML_PATH_TEMPLATE_DISPLAY_SEARCH_BUTTON);
    }

    public function getMinChars(): int
    {
        $minChars = (int)$this->getModuleConfig(self::XML_PATH_TEMPLATE_MIN_CHARS);

        return max(1, $minChars);
    }

    public function getDelay(): float
    {
        return (float) $this->getModuleConfig(self::XML_PATH_TEMPLATE_DELAY);
    }

    public function getPosition(string $ConfigPath): int
    {
        $position = (int)$this->getModuleConfig($ConfigPath);

        return max(1, $position);
    }

    public function getRecentSearchesPosition(): int
    {
        return $this->getPosition(self::XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION);
    }

    public function getPopularSearchesPosition(): int
    {
        return $this->getPosition(self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION);
    }

    public function isPopularSearchesEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED);
    }

    public function isBrowsingHistoryEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_BROWSING_HISTORY_ENABLED);
    }

    public function isRecentSearchesEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED);
    }

    public function isMysqlEngine(): bool
    {
        return $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH) == 'mysql';
    }

    /**
     * @return string[]
     */
    public function getExcludedCmsPagesIdentifiers(): array
    {
        $identifiers = (string)$this->getModuleConfig(self::XML_PATH_EXCLUDED_CMS_PAGES);
        $identifiers = array_map('trim', explode(',', $identifiers));

        return array_filter($identifiers);
    }

    public function isRecentlyViewedEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_RECENT_VIEWED_ENABLED);
    }

    public function getRecentlyViewedBlockTitle(): string
    {
        return (string)$this->getModuleConfig(self::XML_PATH_TEMPLATE_RECENT_VIEWED_TITLE);
    }

    public function getRecentlyViewedBlockLimit(?int $storeId = null): int
    {
        return (int)$this->getModuleConfig(self::XML_PATH_TEMPLATE_RECENT_VIEWED_LIMIT, $storeId);
    }

    public function isRedirectToCartEnabled(): bool
    {
        return $this->getFlagConfig(self::XML_PATH_TEMPLATE_IS_REDIRECT_TO_CART);
    }

    public function getResultBlockId(): int
    {
        return (int) $this->getModuleConfig(self::XML_PATH_EMPTY_RESULT_BLOCK);
    }

    public function isBestsellersBlockEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_BESTSELLERS_ENABLED);
    }

    public function getBestsellersBlockTitle(): string
    {
        return (string)$this->getModuleConfig(self::XML_PATH_BESTSELLERS_TITLE);
    }

    public function getBestsellersBlockPosition(): int
    {
        return (int)$this->getModuleConfig(self::XML_PATH_BESTSELLERS_POSITION);
    }

    public function getBestsellersBlockProductsLimit(): int
    {
        return (int)$this->getModuleConfig(self::XML_PATH_BESTSELLERS_LIMIT);
    }

    public function getCookieLifeTime(int $storeId): int
    {
        return (int)$this->scopeConfig->getValue(Custom::XML_PATH_WEB_COOKIE_COOKIE_LIFETIME);
    }

    public function isEnablePopupIndex(): bool
    {
        return (bool) $this->getModuleConfig(self::XML_PATH_ENABLE_POPUP_INDEX);
    }

    public function getEngine(): string
    {
        return (string) $this->scopeConfig->getValue('catalog/search/engine');
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\IsElasticEngine
     */
    public function isElasticEngine(): bool
    {
        return ObjectManager::getInstance()->get(\Amasty\Xsearch\Model\IsElasticEngine::class)->execute();
    }

    public function isAmastyElasticEngine(): bool
    {
        return strpos($this->getEngine(), 'amasty_elastic') !== false;
    }

    public function getConnectionData(array $testData = []): array
    {
        $path = $this->getBasepathToConfig();

        $defaultData = [
            'hostname' => $this->scopeConfig->getValue($path . 'server_hostname') ?: 'localhost',
            'port' => $this->scopeConfig->getValue($path . 'server_port') ?: '9200',
            'index' => $this->scopeConfig->getValue($path . 'index_prefix') ?: 'magento2',
            'enableAuth' => $this->scopeConfig->getValue($path . 'enable_auth') ?: 0,
            'username' => $this->scopeConfig->getValue($path . 'username'),
            'password' => $this->scopeConfig->getValue($path . 'password'),
            'timeout' => $this->scopeConfig->getValue($path . 'server_timeout') ?: 15,
        ];

        return array_merge($defaultData, $testData);
    }

    public function getIndexName(string $indexType, int $storeId): string
    {
        if ($indexType == 'catalogsearch_fulltext') {
            $indexType = 'product';
        }
        $path = $this->getBasepathToConfig();

        return $this->scopeConfig->getValue($path . 'index_prefix') . '_' . $indexType . '_' . $storeId;
    }

    private function getBasepathToConfig(): string
    {
        return $this->isAmastyElasticEngine()
            ? self::XML_PATH_AMASTY_ELASTIC_CONNECTION
            : self::XML_PATH_CATALOG_SEARCH . $this->getEngine() . '_';
    }

    public function isProductBlockEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_PRODUCT_ENABLED);
    }

    public function getLimit(?int $storeId = null): int
    {
        return max(1, (int)$this->getModuleConfig(self::XML_PATH_TEMPLATE_PRODUCT_LIMIT, $storeId));
    }

    public function isApplyRelevanceRulesInPopup(): bool
    {
        return (bool) $this->getModuleConfig(self::XML_PATH_TEMPLATE_ENABLE_RELEVANCE_RULES_IN_POPUP);
    }

    public function getColorSettings(): array
    {
        if ($this->getModuleConfig(self::XML_PATH_LAYOUT_ENABLED)) {
            return [
                'border' => $this->getModuleConfig(self::XML_PATH_LAYOUT_BORDER),
                'hover' => $this->getModuleConfig(self::XML_PATH_LAYOUT_HOVER),
                'highlight' => $this->getModuleConfig(self::XML_PATH_LAYOUT_HIGHLIGHT),
                'background' => $this->getModuleConfig(self::XML_PATH_LAYOUT_BACKGROUND),
                'text' => $this->getModuleConfig(self::XML_PATH_LAYOUT_TEXT),
                'hover_text' => $this->getModuleConfig(self::XML_PATH_LAYOUT_HOVER_TEXT),
                'search_button' => $this->getModuleConfig(self::XML_PATH_LAYOUT_SEARCH_BUTTON),
                'search_button_text' => $this->getModuleConfig(self::XML_PATH_LAYOUT_SEARCH_BUTTON_TEXT)
            ];
        }

        return [];
    }

    public function getExcludeCategoryIds(?int $storeId = null): string
    {
        return (string)$this->getModuleConfig(self::XML_PATH_TEMPLATE_CATEGORY_EXCLUDE, $storeId);
    }

    /**
     * @return int[]|null
     */
    public function getExcludeCategoryIdsArray(?int $storeId = null): ?array
    {
        $categoryIds = $this->getExcludeCategoryIds($storeId);
        if (empty($categoryIds)) {
            return null;
        }
        $categoryIds = array_map('intval', array_filter(explode(',', $categoryIds)));

        if (!empty($categoryIds) && !in_array(0, $categoryIds, true)) {
            return $categoryIds;
        }

        return null;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isDisplayFullCategoryPath(?int $storeId = null): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_DISPLAY_FULL_CATEGORY_PATH, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isFullScreenEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_FULL_SCREEN, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getPopupViewType(?int $storeId = null): int
    {
        return (int)$this->getModuleConfig(self::XML_PATH_POPUP_VIEW_TYPE, $storeId);
    }

    public function isShowSku(?int $storeId = null): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_SHOW_SKU, $storeId);
    }

    public function isSearchSaveInputValueEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_IS_SAVE_SEARCH_INPUT_VALUE_ENABLED, $storeId);
    }

    public function isSeoUrlsEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_IS_SEO_URL_ENABLED);
    }

    public function getSeoKey(): string
    {
        return (string)trim($this->getModuleConfig(self::XML_PATH_SEO_KEY));
    }

    public function getSearchAttributesJson(): ?string
    {
        return $this->getModuleConfig('search_attributes/attributes');
    }
}
