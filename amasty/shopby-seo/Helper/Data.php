<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Helper;

use Amasty\Base\Model\Serializer;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\Collection;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory;
use Amasty\ShopbySeo\Helper\Url as UrlHelper;
use Amasty\ShopbySeo\Model\Attribute\GetAttributeCodesSeoByDefault;
use Amasty\ShopbySeo\Model\ConfigProvider;
use Amasty\ShopbySeo\Model\Source\GenerateSeoUrl;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * @deprecared usage of helpers is deprecated
 * @see \Amasty\ShopbySeo\Model\ConfigProvider for configs
 */
class Data extends AbstractHelper
{
    public const CANONICAL_ROOT = 'amasty_shopby_seo/canonical/root';
    public const CANONICAL_CATEGORY = 'amasty_shopby_seo/canonical/category';
    public const AMASTY_SHOPBY_SEO_URL_SPECIAL_CHAR = 'amasty_shopby_seo/url/special_char';
    public const AMASTY_SHOPBY_SEO_URL_ATTRIBUTE_NAME = 'amasty_shopby_seo/url/attribute_name';
    public const AMASTY_SHOPBY_SEO_URL_FILTER_WORD = 'amasty_shopby_seo/url/filter_word';
    public const AMSHOPBY_ROOT_GENERAL_URL = 'amshopby_root/general/url';
    public const AMSHOPBY_SEO_PAGE_META_TITLE = 'amasty_shopby_seo/other/page_meta_title';
    public const AMSHOPBY_SEO_PAGE_META_DESCR = 'amasty_shopby_seo/other/page_meta_descriprion';
    public const SKIP_REQUEST_FLAG = 'shopby_seo_skip_request_flag';
    public const SEO_REDIRECT_FLAG = 'shopby_seo_redirect_flag';
    public const SEO_REDIRECT_MISSED_SUFFIX_FLAG = 'shopby_seo_missed_suffix_redirect_flag';
    public const HAS_PARSED_PARAMS = 'shopby_seo_has_parsed_params_flag';
    public const HAS_ROUTE_PARAMS = 'shopby_seo_has_route_params_flag';
    public const IS_MODULE_ENABLED = 'amasty_shopby_seo/url/mode';

    public const CATEGORY_ATTRIBUTE_CODE = 'category_ids';

    /**
     * @var CollectionFactory
     */
    private $settingCollectionFactory;

    /**
     * @var  StoreManager
     */
    private $storeManager;

    /**
     * @var array|null
     */
    private $seoSignificantAttributeCodes;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var array
     */
    private $skipRequestIdentifiers = [
        'catalog/category/',
        'catalog/product/',
        'cms/page/',
        'amasty_xsearch/',
        'customer/',
        'checkout/',
        'catalogsearch',
        'stores/store'
    ];

    /**
     * @var array
     */
    private $attributeUrlAliases = [];

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var GetAttributeCodesSeoByDefault
     */
    private $getAttributeCodesSeoByDefault;

    public function __construct(
        Context $context,
        CollectionFactory $settingCollectionFactory,
        StoreManager $storeManager,
        Config $configHelper,
        UrlFinderInterface $urlFinder,
        Serializer $serializer,
        GetAttributeCodesSeoByDefault $getAttributeCodesSeoByDefault,
        array $skipRequestIdentifiers = []
    ) {
        parent::__construct($context);
        $this->settingCollectionFactory = $settingCollectionFactory;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->urlFinder = $urlFinder;
        $this->skipRequestIdentifiers = array_merge($this->skipRequestIdentifiers, $skipRequestIdentifiers);
        $this->serializer = $serializer;
        $this->getAttributeCodesSeoByDefault = $getAttributeCodesSeoByDefault;
    }

    public function getSeoSignificantAttributeCodes(): array
    {
        if ($this->seoSignificantAttributeCodes === null) {
            $this->seoSignificantAttributeCodes = [];
            if ($this->configHelper->isSeoUrlEnabled()) {
                /** @var Collection $collection */
                $collection = $this->settingCollectionFactory->create();
                $yesValue = $this->configHelper->isGenerateSeoByDefault()
                    ? [GenerateSeoUrl::YES, GenerateSeoUrl::USE_DEFAULT]
                    : [GenerateSeoUrl::YES];
                $collection->addFieldToFilter(FilterSettingInterface::IS_SEO_SIGNIFICANT, $yesValue);
                $collection->addFieldToFilter(
                    ['main_table.' . FilterSettingInterface::ATTRIBUTE_CODE, FilterSettingInterface::IS_MULTISELECT],
                    [
                        ['neq' => self::CATEGORY_ATTRIBUTE_CODE],
                        ['eq' => 1]
                    ]
                );
                $collection->addIsFilterableFilter();
                $this->setAttributeUrlAliases($collection);

                if ($this->configHelper->isGenerateSeoByDefault()) {
                    $this->seoSignificantAttributeCodes = array_unique(array_merge(
                        $this->seoSignificantAttributeCodes,
                        $this->getAttributeCodesSeoByDefault->execute()
                    ));
                }
            }
        }

        return $this->seoSignificantAttributeCodes;
    }

    private function setAttributeUrlAliases(Collection $collection): void
    {
        foreach ($collection->getData() as $filterData) {
            $attributeCode = $filterData['attribute_code'];
            $this->seoSignificantAttributeCodes[] = $attributeCode;
            $aliases = $this->serializer->unserialize($filterData['attribute_url_alias']);
            if ($aliases) {
                $aliases = array_filter($aliases);
            }
            if ($attributeCode === self::CATEGORY_ATTRIBUTE_CODE) {
                $this->injectCategoryAlias($aliases ?: []);
            } else {
                $this->attributeUrlAliases[$attributeCode] = $aliases;
            }
        }
    }

    private function injectCategoryAlias(array $aliases): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            if (!isset($aliases[$storeId]) || !$aliases[$storeId]) {
                $aliases[$storeId] = UrlHelper::CATEGORY_FILTER_PARAM;
            }
        }
        $this->attributeUrlAliases[UrlHelper::CATEGORY_FILTER_PARAM] = $aliases;
    }

    public function getAttributeUrlAliases(): array
    {
        $this->getSeoSignificantAttributeCodes(); // init
        return $this->attributeUrlAliases;
    }

    public function clear(): void
    {
        $this->seoSignificantAttributeCodes = null;
        $this->attributeUrlAliases = [];
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function isAttributeSeoSignificant($attribute)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute) {
            $attribute = $attribute->getAttributeCode();
        }

        if ($attribute == UrlHelper::CATEGORY_FILTER_PARAM) {
            $attribute = self::CATEGORY_ATTRIBUTE_CODE;
        }

        $codes = $this->getSeoSignificantAttributeCodes();
        return in_array($attribute, $codes);
    }

    /**
     * @deprecated
     * @see ConfigProvider::getSpecialChar()
     * @return string
     */
    public function getSpecialChar()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getSpecialChar();
    }

    /**
     * @return mixed
     */
    public function getCanonicalRoot()
    {
        return $this->scopeConfig->getValue(self::CANONICAL_ROOT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCanonicalCategory()
    {
        return $this->scopeConfig->getValue(self::CANONICAL_CATEGORY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGeneralUrl()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_ROOT_GENERAL_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @deprecated
     * @see ConfigProvider::isIncludeAttributeName()
     * @return bool
     */
    public function isIncludeAttributeName()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->isIncludeAttributeName();
    }

    /**
     * @return string
     */
    public function getFilterWord()
    {
        return $this->scopeConfig->getValue(self::AMASTY_SHOPBY_SEO_URL_FILTER_WORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function isAddPageToMetaTitleEnabled()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_SEO_PAGE_META_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function isAddPageToMetaDescriprionEnabled()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_SEO_PAGE_META_DESCR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * @deprecated
     * @see \Amasty\ShopbySeo\Model\UrlParser\Url\IsAllowed::execute
     *
     * @param RequestInterface $request
     * @param bool $allowEmptyModuleName = false
     * @return bool
     */
    public function isAllowedRequest(RequestInterface $request, $allowEmptyModuleName = false)
    {
        if (!$allowEmptyModuleName && !$request->getModuleName()) {
            return false;
        }

        $identifier = ltrim($request->getPathInfo(), '/');

        /** @var \Amasty\ShopbySeo\Model\UrlParser\Url\IsAllowed $isAllowedMethod */
        $isAllowedMethod = ObjectManager::getInstance()->get(\Amasty\ShopbySeo\Model\UrlParser\Url\IsAllowed::class);
        return $isAllowedMethod->execute($identifier);
    }

    private function skipXsearchIdentifier()
    {
        if ($this->isModuleOutputEnabled('Amasty_Xsearch')
            && $this->configHelper->getConfig('amasty_xsearch/general/enable_seo_url')
        ) {
            $this->skipRequestIdentifiers[] = $this->configHelper->getConfig('amasty_xsearch/general/seo_key');
        }
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return !!$this->scopeConfig->getValue(self::IS_MODULE_ENABLED, ScopeInterface::SCOPE_STORE);
    }
}
