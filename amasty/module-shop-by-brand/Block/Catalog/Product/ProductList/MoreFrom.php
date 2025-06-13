<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Catalog\Product\ProductList;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class MoreFrom extends AbstractProduct implements IdentityInterface
{
    public const DEFAULT_PRODUCT_LIMIT = 7;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|array
     */
    private $itemCollection = [];

    /**
     * @var Status
     */
    private $productStatus;

    /**
     * @var Visibility
     */
    private $productVisibility;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var \Amasty\ShopbyBrand\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\ShopbyBrand\Model\Attribute
     */
    private $brandAttribute;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Stock $stockHelper,
        Status $productStatus,
        Visibility $productVisibility,
        PostHelper $postHelper,
        \Amasty\ShopbyBrand\Model\ConfigProvider $configProvider,
        \Amasty\ShopbyBrand\Model\Attribute $brandAttribute,
        UrlHelper $urlHelper,
        array $data = [],
        HttpContext $httpContext = null// TODO move to not optional
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockHelper = $stockHelper;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->postHelper = $postHelper;
        $this->configProvider = $configProvider;
        $this->brandAttribute = $brandAttribute;
        $this->urlHelper = $urlHelper;
        // OM for backward compatibility
        $this->httpContext = $httpContext ?? ObjectManager::getInstance()->get(HttpContext::class);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();

        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 86400);
        }
    }

    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        if ($this->configProvider->getBrandAttributeCode()) {
            $cacheKeyInfo['product_id'] = $this->getProduct()->getId();
            $cacheKeyInfo[] = 'cust_gr' .  $this->getCurrentCustomerGroupId();
        }

        return $cacheKeyInfo;
    }

    public function getIdentities(): array
    {
        $attribute = $this->brandAttribute->getAttribute();
        if ($attribute === null) {
            return [];
        }

        $arrayOfIdentities = [[\Amasty\ShopbyBase\Model\OptionSetting::CACHE_TAG]];
        $arrayOfIdentities[] = $attribute->getIdentities();
        $arrayOfIdentities[] = $this->getProduct()->getIdentities();

        return array_merge(...$arrayOfIdentities);
    }

    public function getItems(): array
    {
        $items = [];
        if (!$this->itemCollection) {
            $this->_prepareData();
        }

        if ($this->itemCollection) {
            $items = $this->itemCollection->getItems();
            shuffle($items);
        }

        return $items;
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        $attributeValue = $this->getBrandValue();

        if (!$attributeValue) {
            return $this;
        }
        $attributeValue = explode(',', $attributeValue);

        $this->initProductCollection($attributeValue);

        return $this;
    }

    private function getBrandValue(): string
    {
        $product = $this->getProduct();
        $attributeCode = $this->configProvider->getBrandAttributeCode();
        $attributeValue = $product->getData($attributeCode);

        if (!$attributeValue || !$attributeCode) {
            return '';
        }

        return (string) $attributeValue;
    }

    private function initProductCollection(array $attributeValue): void
    {
        $currentProductId = (int) $this->getProduct()->getId();
        $attributeCode = $this->configProvider->getBrandAttributeCode();

        $this->itemCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['small_image', 'name'])
            ->addAttributeToFilter($attributeCode, ['in' => $attributeValue])
            ->addFieldToFilter('entity_id', ['neq' => $currentProductId])
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('special_to_date')
            ->setPageSize($this->getProductsLimit());

        $this->stockHelper->addInStockFilterToCollection($this->itemCollection);
        $this->itemCollection->setCurPage(random_int(1, max($this->itemCollection->getLastPageNumber() - 1, 1)));

        $this->itemCollection->load();

        foreach ($this->itemCollection->getItems() as $product) {
            $product->setDoNotUseCategoryId(true);
        }
    }

    /**
     * @return int
     */
    private function getProductsLimit()
    {
        return $this->configProvider->getMoreFromProductsLimit($this->getStoreId()) ? : self::DEFAULT_PRODUCT_LIMIT;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isEnabled() && $this->getItems()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->configProvider->isMoreFromEnabled($this->getStoreId());
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle()
    {
        $title = $this->configProvider->getTitleMoreFrom($this->getStoreId());
        preg_match_all('@\{(.+?)\}@', $title, $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $value = '';
                switch ($match) {
                    case 'brand_name':
                        $value = $this->getBrandName();
                        break;
                }
                $title = str_replace('{' . $match . '}', $value, $title);
            }
        }

        $title = $title ?: __('More from this Brand');

        return $title;
    }

    /**
     * Retrieve product post data for buy request
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPostData(\Magento\Catalog\Model\Product $product): string
    {
        $postData = ['product' => $product->getEntityId()];
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            $url = $product->getProductUrl();
            $postData[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->urlHelper->getEncodedUrl($url);
        }

        return $this->getPostHelper()->getPostData(
            $this->getAddToCartUrl($product),
            $postData
        );
    }

    /**
     * @return string
     */
    private function getBrandName()
    {
        $value = '';
        $attribute = $this->brandAttribute->getAttribute();
        if ($attribute && $attribute->usesSource()) {
            $attributeValue = $this->getBrandValue();
            $value = $attribute->getSource()->getOptionText($attributeValue);
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return $value;
    }

    /**
     * @return PostHelper
     */
    public function getPostHelper()
    {
        return $this->postHelper;
    }

    /**
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    public function getCompareHelper()
    {
        return $this->_compareProduct;
    }

    /**
     * @return \Magento\Wishlist\Helper\Data
     */
    public function getWishlistHelper()
    {
        return $this->_wishlistHelper;
    }

    /**
     * @return int
     */
    private function getStoreId(): int
    {
        return (int) $this->_storeManager->getStore()->getId();
    }

    private function getCurrentCustomerGroupId(): int
    {
        return (int)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
    }
}
