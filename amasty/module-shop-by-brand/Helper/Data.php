<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Helper;

use Amasty\Base\Model\Di\Wrapper as ShopBySeoConfigDi;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionCollectionFactory;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbySeo\Helper\Config as ShopBySeoConfig;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @deprecated moved to separate class
     */
    public const DEFAULT_CATEGORY_LOGO_SIZE = ConfigProvider::DEFAULT_CATEGORY_LOGO_SIZE;

    public const PATH_BRAND_URL_KEY = 'amshopby_brand/general/url_key';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var array
     */
    private $brandAliases = [];

    /**
     * @var \Amasty\ShopbyBrand\Model\Attribute
     */
    private $brandAttribute;

    /**
     * @var ShopBySeoConfigDi|ShopBySeoConfig
     */
    private $shopBySeoConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        OptionCollectionFactory $optionCollectionFactory,
        StoreManagerInterface $storeManager,
        ProductUrl $productUrl,
        \Magento\Framework\Escaper $escaper,
        \Amasty\ShopbyBrand\Model\Attribute $brandAttribute,
        ShopBySeoConfigDi $shopBySeoConfig = null // TODO move to not optional
    ) {
        parent::__construct($context);
        $this->url = $context->getUrlBuilder();
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productUrl = $productUrl;
        $this->escaper = $escaper;
        $this->brandAttribute = $brandAttribute;
        $this->shopBySeoConfig = $shopBySeoConfig ?? ObjectManager::getInstance()->get(ShopBySeoConfigDi::class);
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getAllBrandsUrl($scopeCode = null)
    {
        return $this->url->getUrl($this->getIdentifier($scopeCode));
    }

    /**
     * @param $scopeCode
     * @return string
     */
    private function getIdentifier($scopeCode)
    {
        $pageIdentifier = $this->scopeConfig->getValue(
            'amshopby_brand/general/brands_page',
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        $identifierWithId = explode('|', $pageIdentifier);

        return $identifierWithId[0];
    }

    public function getBrandAliases(int $storeId = null): array
    {
        $storeId = $storeId ?: (int)$this->storeManager->getStore()->getId();
        if (!isset($this->brandAliases[$storeId])) {
            $attributeCode = $this->getBrandAttributeCode();

            if ($attributeCode == '') {
                return [];
            }
            $options = $this->brandAttribute->getOptions($storeId);

            if (empty($options)) {
                return [];
            }

            $items = [];

            foreach ($options as $option) {
                $items[$option->getValue()] = str_replace(
                    '-',
                    $this->getSpecialChar(),
                    $this->productUrl->formatUrlKey($option->getLabel())
                );
            }

            $this->brandAliases[$storeId] = $this->getStoreAliases($items, $storeId);
        }

        return $this->brandAliases[$storeId];
    }

    /**
     * @deprecated
     * @see \Amasty\ShopbyBrand\Model\ConfigProvider::getSuffix()
     * @return string
     */
    public function getSuffix()
    {
        $suffix = '';

        if ($this->scopeConfig->isSetFlag('amasty_shopby_seo/url/add_suffix_shopby')) {
            $suffix = (string)$this->scopeConfig
                ->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);
        }

        return $suffix;
    }

    /**
     * @param $filters
     * @return int|string|null
     */
    public function getBrandAttributeKey($filters)
    {
        $brandAttributeCode = $this->getBrandAttributeCode();

        foreach ($filters as $key => $filter) {
            $filterAttribute = $filter['setting']->getData('attribute_model');

            if ($filterAttribute && $filterAttribute->getAttributeCode() == $brandAttributeCode) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return string
     * @deprecared moved to ConfigProvider
     * @see \Amasty\ShopbyBrand\Model\ConfigProvider::getBrandAttributeCode
     */
    public function getBrandAttributeCode()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getBrandAttributeCode();
    }

    public function getBrandUrlKey(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::PATH_BRAND_URL_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param Option $option
     * @param int $storeId
     * @param bool $addBaseUrl
     * @return string
     */
    public function getBrandUrl(Option $option, int $storeId = null, bool $addBaseUrl = true): string
    {
        $url = '#';
        $aliases = $this->getBrandAliases($storeId);

        if (isset($aliases[$option->getValue()])) {
            $brandAlias = $aliases[$option->getValue()];
            $urlKey = $this->getBrandUrlKey($storeId);
            $urlSuffix = $this->getSuffix();
            $url = (!!$urlKey ? $urlKey . '/' . $brandAlias : $brandAlias) . $urlSuffix;
            $url = $addBaseUrl ? $this->_urlBuilder->getBaseUrl(['_scope' => $storeId]) . $url : $url;
        }

        return $url;
    }

    /**
     * @param array $defaultAliases
     * @param int $storeId
     * @return array
     */
    private function getStoreAliases($defaultAliases, $storeId)
    {
        $storeIds = [
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            $storeId
        ];
        $collection = $this->optionCollectionFactory->create();
        $collection->addFieldToFilter(
            OptionSettingInterface::ATTRIBUTE_CODE,
            ['eq' => $this->getBrandAttributeCode()]
        )
            ->addFieldToFilter('value', ['in' => array_keys($defaultAliases)])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addFieldToFilter('url_alias', ['neq' => ''])
            ->setOrder('store_id', AbstractDb::SORT_ORDER_ASC);

        foreach ($collection as $item) {
            $formatAlias = $this->productUrl->formatUrlKey($item->getUrlAlias());
            $defaultAliases[$item->getValue()] = str_replace('-', $this->getSpecialChar(), $formatAlias);
        }

        return $defaultAliases;
    }

    public function getModuleConfig(string $path): string
    {
        return $this->scopeConfig->getValue('amshopby_brand/' . $path, ScopeInterface::SCOPE_STORE) ?: '';
    }

    /**
     * @return int|string
     */
    public function isTopmenuEnabled()
    {
        return $this->getModuleConfig('general/topmenu_enabled');
    }

    /**
     * @param array|\Amasty\ShopbyBrand\Model\Brand\BrandDataInterface $item
     * @return string
     */
    public function generateToolTipContent($item)
    {
        $content = '';
        $template = $this->getModuleConfig('general/tooltip_content');
        $template = $this->replaceCustomVariables($template, $item);
        $template = trim($template);

        if ($template) {
            $template = $this->escaper->escapeHtml($template);
            $content = 'data-amshopby-js="brand-tooltip" data-tooltip-content="' . $template . '"';
        }

        return $content;
    }

    /**
     * @param string $template
     * @param array|\Amasty\ShopbyBrand\Model\Brand\BrandDataInterface $item
     * @return string
     */
    private function replaceCustomVariables($template, $item)
    {
        preg_match_all('@\{(.+?)\}@', $template, $matches);

        if (isset($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $match) {
                $value = '';
                switch ($match) {
                    case 'title':
                        if (isset($item['label'])) {
                            $value = '<h3>' . $item['label'] . '</h3>';
                        }
                        break;
                    case 'description':
                    case 'short_description':
                        if (isset($item[$match]) && $item[$match]) {
                            $value = '<p>' . $item[$match] . '</p>';
                        }
                        break;
                    case 'small_image':
                    case 'image':
                        $imgUrl = $match == 'small_image' ? $item['img'] : $item['image'];
                        if (isset($imgUrl)) {
                            $value = sprintf(
                                '<img class=\'am-brand-%s\' src=\'%s\' alt=\'%s\'/>',
                                $match,
                                $imgUrl,
                                __('Brand Image')->render()
                            );
                        }
                        break;
                }
                $template = str_replace('{' . $match . '}', $value, $template);
            }
        }

        return strip_tags($template, '<img><p><h3><b><strong>');
    }

    public function getSpecialChar(): string
    {
        if ($this->_moduleManager->isEnabled('Amasty_ShopbySeo')
            && $this->shopBySeoConfig->isSeoUrlEnabled()
        ) {
            return $this->scopeConfig->getValue('amasty_shopby_seo/url/special_char', ScopeInterface::SCOPE_STORE);
        } else {
            return '-';
        }
    }

    /**
     * @return string
     */
    public function getBrandLabel()
    {
        return (string)$this->scopeConfig->getValue(
            'amshopby_brand/general/menu_item_label',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * used in graphQL
     * @return int|string
     * @deprecated
     */
    public function getLogoProductPageWidth()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getLogoWidth();
    }

    /**
     * used in graphQL
     * @return int|string
     * @deprecated
     */
    public function getLogoProductPageHeight()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getLogoHeight();
    }

    /**
     * @return int|string
     * @deprecated
     */
    public function getBrandLogoProductListingWidth()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getListingBrandLogoWidth();
    }

    /**
     * @return int|string
     * @deprecated
     */
    public function getBrandLogoProductListingHeight()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getListingBrandLogoHeight();
    }
}
