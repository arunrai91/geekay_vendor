<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Helper;

use Amasty\Xsearch\Block\Search\Blog;
use Amasty\Xsearch\Block\Search\Brand;
use Amasty\Xsearch\Block\Search\BrowsingHistory;
use Amasty\Xsearch\Block\Search\Category;
use Amasty\Xsearch\Block\Search\Faq;
use Amasty\Xsearch\Block\Search\Landing;
use Amasty\Xsearch\Block\Search\Locator;
use Amasty\Xsearch\Block\Search\Page;
use Amasty\Xsearch\Block\Search\Popular;
use Amasty\Xsearch\Block\Search\Product;
use Amasty\Xsearch\Block\Search\Recent;
use Amasty\Xsearch\Model\Config;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match as QueryMatchBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const MODULE_NAME = 'amasty_xsearch/';
    public const XML_PATH_TEMPLATE_CATEGORY_POSITION = 'category/position';
    public const XML_PATH_TEMPLATE_PRODUCT_POSITION = 'product/position';
    public const XML_PATH_TEMPLATE_PAGE_POSITION = 'page/position';
    public const XML_PATH_TEMPLATE_LANDING_POSITION = 'landing_page/position';
    public const XML_PATH_TEMPLATE_BRAND_POSITION = 'brand/position';
    public const XML_PATH_TEMPLATE_BLOG_POSITION = 'blog/position';
    public const XML_PATH_TEMPLATE_FAQ_POSITION = 'faq/position';
    public const XML_PATH_TEMPLATE_LOCATOR_POSITION = 'locator/position';

    public const XML_PATH_TEMPLATE_CATEGORY_ENABLED = 'category/enabled';
    public const XML_PATH_TEMPLATE_PRODUCT_ENABLED = 'product/enabled';
    public const XML_PATH_TEMPLATE_PAGE_ENABLED = 'page/enabled';
    public const XML_PATH_TEMPLATE_LANDING_ENABLED = 'landing_page/enabled';
    public const XML_PATH_TEMPLATE_BRAND_ENABLED = 'brand/enabled';
    public const XML_PATH_TEMPLATE_BLOG_ENABLED = 'blog/enabled';
    public const XML_PATH_TEMPLATE_FAQ_ENABLED = 'faq/enabled';
    public const XML_PATH_TEMPLATE_LOCATOR_ENABLED = 'locator/enabled';

    public const XML_PATH_IS_SINGLE_PRODUCT_REDIRECT = 'product/redirect_single_product';

    /**
     * @deprecated setting moved to Config
     * @see \Amasty\Xsearch\Model\Config
     */
    public const XML_PATH_IS_SEO_URL_ENABLED = 'general/enable_seo_url';

    /**
     * @deprecated setting moved to Config
     * @see \Amasty\Xsearch\Model\Config
     */
    public const XML_PATH_SEO_KEY = 'general/seo_key';
    public const XML_PATH_POPUP_INDEX = 'general/enable_popup_index';

    /**
     * @var CatalogConfig
     */
    private $configAttribute;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var StripTags
     */
    private $stripTags;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var Config
     */
    private $moduleConfigProvider;

    public function __construct(
        CatalogConfig $configAttribute,
        Collection $collection,
        SearchHelper $searchHelper,
        StripTags $stripTags,
        Context $context,
        SessionFactory $sessionFactory,
        Config $moduleConfigProvider
    ) {
        parent::__construct($context);
        $this->configAttribute = $configAttribute;
        $this->collection = $collection;
        $this->searchHelper = $searchHelper;
        $this->stripTags = $stripTags;
        $this->sessionFactory = $sessionFactory;
        $this->moduleConfigProvider = $moduleConfigProvider;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(self::MODULE_NAME . $path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $block
     * @return bool
     */
    public function isIndexEnable($block = null)
    {
        $isIndexEnable = $this->getModuleConfig(self::XML_PATH_POPUP_INDEX);

        return $isIndexEnable || ($block !== null && ($block instanceof \Amasty\Xsearch\Block\Search\Category));
    }

    /**
     * @param $text
     * @param $query
     * @return string
     */
    public function highlight($text, $query)
    {
        if ($query && $text) {
            preg_match_all('~\w+~u', $query, $matches);

            if ($matches && isset($matches[0])) {
                $re = '/(' . implode('|', $matches[0]) . ')/iu';
                $text = preg_replace(
                    $re,
                    '<span class="amsearch-highlight">$0</span>',
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    html_entity_decode($text)
                );
            }
        }

        return $text;
    }

    /**
     * @param string $tabType
     * @return string
     */
    public function getTabTitle(string $tabType)
    {
        return (string)$this->getModuleConfig($tabType . '/title');
    }

    /**
     * @param $position
     * @param $block
     * @param $result
     */
    protected function _pushItem($position, $block, &$result)
    {
        $this->pushItem($position, $block, $result);
    }

    public function pushItem(string $position, Template $block, array &$result)
    {
        $positions = explode('/', $position);
        $type = isset($positions[0]) ? $positions[0] : false;
        $position = $this->getModuleConfig($position) * 10; // x10 - fix sorting issue
        while (isset($result[$position])) {
            $position++;
        }
        $currentHtml = $block->toHtml();

        $result[$position] = [
            'type' =>  $type,
            'html' => $currentHtml
        ];
    }

    /**
     * @param \Magento\Framework\View\Layout $layout
     * @return array
     */
    public function getBlocksHtml(\Magento\Framework\View\Layout $layout)
    {
        $result = [];

        if ($this->moduleConfigProvider->isPopularSearchesEnabled()) {
            $this->pushItem(
                Config::XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION,
                $layout->createBlock(Popular::class, 'amasty.xsearch.search.popular'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_PRODUCT_ENABLED)) {
            /** @var Product $productsBlock */
            $productsBlock = $layout->createBlock(
                Product::class,
                'amasty.xsearch.product',
                []
            );

            $this->pushItem(
                self::XML_PATH_TEMPLATE_PRODUCT_POSITION,
                $productsBlock,
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_CATEGORY_ENABLED)) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_CATEGORY_POSITION,
                $layout->createBlock(Category::class, 'amasty.xsearch.category'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_PAGE_ENABLED)) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_PAGE_POSITION,
                $layout->createBlock(Page::class, 'amasty.xsearch.page'),
                $result
            );
        }

        if ($this->moduleConfigProvider->isRecentSearchesEnabled()) {
            $this->pushItem(
                Config::XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION,
                $layout->createBlock(Recent::class, 'amasty.xsearch.search.recent'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_LANDING_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_Xlanding')
        ) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_LANDING_POSITION,
                $layout->createBlock(Landing::class, 'amasty.xsearch.landing.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_BRAND_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_ShopbyBrand')
        ) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_BRAND_POSITION,
                $layout->createBlock(Brand::class, 'amasty.xsearch.brand.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_BLOG_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_Blog')
        ) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_BLOG_POSITION,
                $layout->createBlock(Blog::class, 'amasty.xsearch.blog.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_LOCATOR_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_StoreLocatorAdvancedSearch')
        ) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_LOCATOR_POSITION,
                $layout->createBlock(Locator::class, 'amasty.xsearch.locator.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_FAQ_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_Faq')
        ) {
            $this->pushItem(
                self::XML_PATH_TEMPLATE_FAQ_POSITION,
                $layout->createBlock(Faq::class, 'amasty.xsearch.faq.page'),
                $result
            );
        }

        if ($this->moduleConfigProvider->isBrowsingHistoryEnabled()) {
            $this->pushItem(
                Config::XML_PATH_BROWSING_HISTORY_POSITION,
                $layout->createBlock(BrowsingHistory::class, 'amasty.xsearch.browsing.history'),
                $result
            );
        }

        ksort($result);

        return $result;
    }

    /**
     * @param string $requiredData
     * @return array
     */
    public function getProductAttributes($requiredData = '')
    {
        if ($requiredData == 'is_searchable') {
            $attributeNames = [];
            foreach ($this->collection->addIsSearchableFilter()->getItems() as $attribute) {
                $attributeNames[] = $attribute->getAttributeCode();
            }

            return $attributeNames;
        } else {
            return $this->collection->getItems();
        }
    }

    public function isSingleProductRedirect()
    {
        return $this->getModuleConfig(self::XML_PATH_IS_SINGLE_PRODUCT_REDIRECT);
    }

    /**
     * @param string $query
     * @return string
     */
    public function getResultUrl($query = null)
    {
        return $this->searchHelper->getResultUrl($query);
    }

    /**
     * @return bool
     */
    public function isNoIndexFollowEnabled()
    {
        return true;
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\Config::isSeoUrlsEnabled
     * @return bool
     */
    public function isSeoUrlsEnabled()
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_IS_SEO_URL_ENABLED);
    }

    /**
     * @deprecated
     * @see \Amasty\Xsearch\Model\Config::getSeoKey
     * @return string
     */
    public function getSeoKey()
    {
        return (string)trim($this->getModuleConfig(self::XML_PATH_SEO_KEY));
    }

    /**
     * @param $query
     * @param string $engine
     * @return mixed
     */
    public function setStrippedQueryText($query, $engine = 'mysql')
    {
        $queryText = $query->getQueryText();

        if (strpos($engine, 'mysql') !== false) {
            //@phpstan-ignore-next-line
            $replaceSymbols = str_split(QueryMatchBuilder::SPECIAL_CHARACTERS, 1);
            $queryText = trim(str_replace($replaceSymbols, ' ', $queryText));
            $query->setQueryText($queryText);
        } else {
            $query->setQueryText($this->stripTags->filter($queryText));
        }

        return $query;
    }

    /**
     * @return string
     */
    public function getCurrentSearchEngineCode()
    {
        return $this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->getCustomerSession()->getCustomerGroupId();
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }
}
