<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Catalog\Product\ProductList;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Helper\Product\Compare as CompareHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Registry;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class MoreFrom extends Template implements IdentityInterface
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

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ImageFactory
     */
    private ImageFactory $imageFactory;

    /**
     * @var WishlistHelper
     */
    private WishlistHelper $wishlistHelper;

    /**
     * @var CompareHelper
     */
    private CompareHelper $compareHelper;

    /**
     * @var CartHelper
     */
    private CartHelper $cartHelper;

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
        ImageFactory $imageFactory,
        Registry $registry,
        WishlistHelper $wishlistHelper,
        CompareHelper $compareHelper,
        CartHelper $cartHelper,
        HttpContext $httpContext,
        array $data = []
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
        $this->registry = $registry;
        $this->imageFactory = $imageFactory;
        $this->wishlistHelper = $wishlistHelper;
        $this->compareHelper = $compareHelper;
        $this->cartHelper = $cartHelper;
        $this->httpContext = $httpContext;
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
            $this->prepareData();
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
    private function prepareData()
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
    private function isEnabled()
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
        if (!empty($matches[1])) {
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

        return $title ?: __('More from this Brand');
    }

    /**
     * Retrieve product post data for buy request
     *
     * @param Product $product
     * @return string
     */
    public function getProductPostData(Product $product): string
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

    public function getCompareHelper(): CompareHelper
    {
        return $this->compareHelper;
    }

    public function getWishlistHelper(): WishlistHelper
    {
        return $this->wishlistHelper;
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

    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->registry->registry('product'));
        }
        return $this->getData('product');
    }

    public function getImage(Product $product, string $imageId, array $attributes = []): Image
    {
        return $this->imageFactory->create($product, $imageId, $attributes);
    }

    public function getAddToCartUrl(Product $product, array $additional = []): string
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }

        return $this->cartHelper->getAddUrl($product, $additional);
    }

    public function getProductUrl(Product $product, array $additional = []): string
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }

            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    public function hasProductUrl(Product $product): bool
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }

        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    public function getProductPrice(Product $product): string
    {
        return $this->getProductPriceHtml(
            $product,
            FinalPrice::PRICE_CODE
        );
    }

    public function getProductPriceHtml(
        Product $product,
        string $priceType,
        string $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ): string {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';
        if ($priceRender) {
            $price = $priceRender->render($priceType, $product, $arguments);
        }

        return $price;
    }
}
