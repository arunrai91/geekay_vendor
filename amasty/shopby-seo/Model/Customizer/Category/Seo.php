<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Customizer\Category;

use Amasty\Shopby\Model\Layer\IsBrandPage;
use Amasty\Shopby\Model\Request;
use Amasty\ShopbyBase\Helper\Data;
use Amasty\ShopbyBase\Model\Category\Manager;
use Amasty\ShopbyBase\Model\Customizer\Category as CategoryCustomizer;
use Amasty\ShopbyBase\Model\Customizer\Category\CustomizerInterface;
use Amasty\ShopbyBase\Model\UrlBuilder;
use Amasty\ShopbySeo\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\Customizer\Category\Seo\FiltersResolver;
use Amasty\ShopbySeo\Model\Source\Canonical\Brand;
use Amasty\ShopbySeo\Model\Source\Canonical\Category as CategoryAlias;
use Amasty\ShopbySeo\Model\Source\Canonical\Root;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\ProductList\Toolbar;

/**
 * Resolve canonical URL for a category entity.
 */
class Seo implements CustomizerInterface
{
    public const PAGE_PARAM_NAME = 'p';

    /**
     * @var array
     */
    private $excludedParams = [
        'product_list_mode',
        'product_list_order',
        'product_list_dir',
        'product_list_limit'
    ];

    /**
     * @var Data
     */
    private $baseHelper;

    /**
     * @var Manager
     */
    private $categoryManager;

    /**
     * @var UrlBuilder
     */
    private $url;

    /**
     * @var Request
     */
    private $amshopbyRequest;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FiltersResolver
     */
    private $filtersResolver;

    /**
     * Needed only for setting canUseCanonicalTag.
     * Probably going to be replaced when Magento refactor helper.
     *
     * @var CategoryHelper
     * @see CategoryHelper::canUseCanonicalTag
     */
    private $categoryHelper;

    /**
     * @var IsBrandPage
     */
    private $isBrandPage;

    public function __construct(
        Data $baseHelper,
        Manager $categoryManager,
        UrlBuilder $url,
        Request $amshopbyRequest,
        ConfigProvider $configProvider,
        FiltersResolver $filtersResolver,
        CategoryHelper $categoryHelper,
        IsBrandPage $isBrandPage
    ) {
        $this->baseHelper = $baseHelper;
        $this->categoryManager = $categoryManager;
        $this->url = $url;
        $this->amshopbyRequest = $amshopbyRequest;
        $this->configProvider = $configProvider;
        $this->filtersResolver = $filtersResolver;
        $this->categoryHelper = $categoryHelper;
        $this->isBrandPage = $isBrandPage;
    }

    /**
     * Prepare canonical URL for currently viewed category page.
     * Category can also be a brand page.
     */
    public function prepareData(Category $category): void
    {
        if (!$this->categoryHelper->canUseCanonicalTag()) {
            return;
        }

        $canonical = $this->getCanonicalUrl($category);

        $category->setData(CategoryCustomizer::ORIGINAL_CATEGORY_URL, $category->getUrl());
        $category->setData('url', $canonical);
    }

    public function getCanonicalUrl(Category $category): string
    {
        if ($this->isBrandPage->execute()) {
            return $this->getBrandModeCanonical();
        }

        if ($this->categoryManager->getRootCategoryId() === $category->getId()) {
            return $this->getRootModeCanonical();
        }

        return $this->getCategoryModeCanonical($category);
    }

    public function getRootModeCanonical(): string
    {
        $canonical = $this->url->getCurrentUrl();

        switch ($this->configProvider->getCanonicalRoot()) {
            case Root::ROOT_CURRENT:
                $canonical = $this->url->getCurrentUrl();
                break;
            case Root::ROOT_PURE:
                $canonical = $this->url->getUrl('amshopby/index/index');
                break;
            case Root::ROOT_FIRST_ATTRIBUTE:
                $canonical = $this->getFirstAttributeValueUrl();
                break;
            case Root::ROOT_CUT_OFF_GET:
                $canonical = $this->stripGetParams($this->url->getCurrentUrl());
                break;
        }

        if ($canonical === null) {
            $canonical = $this->url->getCurrentUrl();
        }

        return $this->prepareCanonicalUrl($canonical);
    }

    public function getBrandModeCanonical(): string
    {
        switch ($this->configProvider->getCanonicalBrand()) {
            case Brand::BRAND_PURE:
                $canonical = $this->getAttributeValueUrl(
                    $this->baseHelper->getBrandAttributeCode()
                );
                break;
            case Brand::BRAND_FIRST_ATTRIBUTE:
                $canonical = $this->getFirstAttributeValueUrl();
                break;
            case Brand::BRAND_CUT_OFF_GET:
                $canonical = $this->stripGetParams($this->url->getCurrentUrl());
                break;
            case Brand::BRAND_CURRENT:
            default:
                $canonical = $this->url->getCurrentUrl();
                break;
        }

        if ($canonical === null) {
            $canonical = $this->url->getCurrentUrl();
        }

        return $this->prepareCanonicalUrl($canonical);
    }

    public function getCategoryModeCanonical(Category $category): string
    {
        $canonical = null;

        switch ($this->configProvider->getCanonicalCategory()) {
            case CategoryAlias::CATEGORY_CURRENT:
                $canonical = $this->url->getCurrentUrl(false);
                break;
            case CategoryAlias::CATEGORY_PURE:
                $canonical = $this->getCurrentWithoutFilters($category);
                break;
            case CategoryAlias::CATEGORY_BRAND_FILTER:
                $canonical = $this->getAttributeValueUrl(
                    $this->baseHelper->getBrandAttributeCode()
                );
                break;
            case CategoryAlias::CATEGORY_FIRST_ATTRIBUTE:
                $canonical = $this->getFirstAttributeValueUrl();
                break;
            case CategoryAlias::CATEGORY_CUT_OFF_GET:
                $canonical = $this->stripGetParams($this->url->getCurrentUrl(false));
                break;
        }

        if ($canonical === null) {
            $canonical = $category->getUrl();
        }

        return $this->prepareCanonicalUrl($canonical);
    }

    /**
     * @param $url
     * @return string
     */
    private function prepareCanonicalUrl($url)
    {
        $pos = max(0, strpos($url, '?'));
        if ($pos) {
            $urlParts = explode('?', $url);
            if (isset($urlParts[0])) {
                $url = $urlParts[0];
                if (isset($urlParts[1])) {
                    // @codingStandardsIgnoreLine
                    parse_str($urlParts[1], $params);
                    foreach ($this->excludedParams as $param) {
                        unset($params[$param]);
                    }
                    if (isset($params[self::PAGE_PARAM_NAME]) && $params[self::PAGE_PARAM_NAME] <= 1) {
                        unset($params[self::PAGE_PARAM_NAME]);
                    }
                    if ($params) {
                        $url .= '?' . http_build_query($params);
                    }
                }
            }
        } else {
            $params = $this->amshopbyRequest->getRequestParams();
            $page = isset($params['p']) ? array_shift($params['p']) : null;
            $page = (int)$page;
            $url .= $page && $page !== 1 ? '?p=' . $page : '';
        }

        return $url;
    }

    /**
     * @param $category
     * @return string|null
     */
    private function getCurrentWithoutFilters($category)
    {
        $params = $this->amshopbyRequest->getRequestParams();
        $page = isset($params['p']) ? array_shift($params['p']) : null;
        $page = (int)$page;

        return $page && $page !== 1 ? $category->getUrl() . '?p=' . $page : null;
    }

    /**
     * @param $url
     * @return string
     */
    public function stripGetParams($url)
    {
        $pos = max(0, strpos($url, '?'));
        if ($pos) {
            $url = substr($url, 0, $pos);
        }

        return $url;
    }

    /**
     * @return string
     */
    protected function getFirstAttributeValueUrl(): string
    {
        $appliedFilters = $this->filtersResolver->getAppliedFilters();
        $brandAttrCode = $this->baseHelper->getBrandAttributeCode();
        $query = [];

        foreach ($appliedFilters as $filter) {
            $requestVar = $filter->getRequestVar();

            if ($requestVar === $brandAttrCode) {
                continue;
            } elseif (empty($query)) {
                $query[$requestVar] = $this->filtersResolver->getAppliedFilterValue($filter);
            } else {
                $query[$requestVar] = null;
            }
        }

        $query[Toolbar::ORDER_PARAM_NAME] = null;
        $query[Toolbar::LIMIT_PARAM_NAME] = null;
        $query[Toolbar::MODE_PARAM_NAME] = null;
        $query[Toolbar::DIRECTION_PARAM_NAME] = null;

        return $this->url->getUrl(
            '*/*/*',
            ['_current' => true, '_use_rewrite' => true, '_query' => $query]
        );
    }

    protected function getAttributeValueUrl(string $attributeCode): ?string
    {
        $appliedFilter = $this->filtersResolver->getFilterByCode($attributeCode);
        if ($appliedFilter === null) {
            return null;
        }
        $query = [];
        foreach ($this->filtersResolver->getAppliedFilters() as $filter) {
            $query[$filter->getRequestVar()] = null;
        }

        $query[$appliedFilter->getRequestVar()] = $this->filtersResolver->getAppliedFilterValue($appliedFilter);

        return $this->url->getUrl(
            '*/*/*',
            ['_current' => true, '_use_rewrite' => true, '_query' => $query]
        );
    }
}
