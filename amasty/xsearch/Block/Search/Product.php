<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Block\Search;

use Amasty\Xsearch\Controller\RegistryConstants;
use Amasty\Xsearch\Model\Di\Wrapper;
use Amasty\Xsearch\Model\Search\SearchAdapterResolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\ViewModel\Product\OptionsData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Product extends ListProduct
{
    public const BLOCK_TYPE = 'product';
    public const MEDIA_URL_PLACEHOLDER = '$$XSEARCH_MEDIA_URL$$';
    public const XML_PATH_TEMPLATE_PRODUCT_LIMIT = 'product/limit';
    public const XML_PATH_TEMPLATE_TITLE = 'product/title';
    public const XML_PATH_TEMPLATE_NAME_LENGTH = 'product/name_length';
    public const XML_PATH_TEMPLATE_DESC_LENGTH = 'product/desc_length';
    public const XML_PATH_TEMPLATE_REVIEWS = 'product/reviews';
    public const XML_PATH_TEMPLATE_ADD_TO_CART = 'product/add_to_cart';

    public const SMARTWAVE_PORTO_CODE = 'Smartwave/porto';

    public const IMAGE_ID = 'amasty_xsearch_page_list';

    public const PORTO_IMAGE_ID = 'amasty_xsearch_page_list_porto';

    public const IMAGE_WITH_BORDERS_TEMPLATE = 'Amasty_Xsearch::product/image_with_borders.phtml';

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $string;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $xSearchHelper;

    /**
     * @var RedirectInterface
     */
    private $redirector;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array|null
     */
    private $products;

    /**
     * @var string|null
     */
    private $mediaUrl;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    private $wishlistHelper;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var SearchAdapterResolver
     */
    private $searchAdapterResolver;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var \Amasty\Xsearch\Model\Config
     */
    private $config;

    /**
     * @var Wrapper|OptionsData
     */
    private $productOptionsProvider;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Amasty\Xsearch\Helper\Data $xSearchHelper,
        RedirectInterface $redirector,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Url $urlBuilder,
        SearchAdapterResolver $searchAdapterResolver,
        HttpContext $httpContext,
        \Amasty\Xsearch\Model\Config $config,
        array $data = [],
        ?Wrapper $productOptionsProvider = null // TODO move to not optional
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
        $this->string = $string;
        $this->formKey = $formKey;
        $this->xSearchHelper = $xSearchHelper;
        $this->redirector = $redirector;
        $this->storeManager = $context->getStoreManager();
        $this->stockHelper = $stockHelper;
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->wishlistHelper = $wishlistHelper;
        $this->design = $design;
        $this->_urlBuilder = $urlBuilder;
        $this->httpContext = $httpContext;
        $this->setData('cache_lifetime', AbstractSearch::DEFAULT_CACHE_LIFETIME);
        $this->searchAdapterResolver = $searchAdapterResolver;
        $this->config = $config;
        $this->productOptionsProvider = $productOptionsProvider ?? ObjectManager::getInstance()->create(
            Wrapper::class,
            ['name' => OptionsData::class]
        );
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();
        return array_merge(
            [
                $this->getQuery()->getQueryText(),
                'group' => $this->xSearchHelper->getCustomerGroupId(),
                HttpContext::CONTEXT_CURRENCY => $this->httpContext->getValue(HttpContext::CONTEXT_CURRENCY)
            ],
            $cacheKey
        );
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [AbstractSearch::DEFAULT_CACHE_TAG, AbstractSearch::DEFAULT_CACHE_TAG . '_' . $this->getBlockType()];
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_template = 'Amasty_Xsearch::search/product_list.phtml';
        parent::_construct();
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    public function prepareSortableFieldsByCategory($category)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::BLOCK_TYPE;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setIndexedIds(array $ids)
    {
        return $this->setData('indexed_ids', $ids);
    }

    /**
     * @inheritdoc
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            if (!$this->getIndexedIds()) {
                $this->_productCollection = $this->initializeProductCollection();
            } else {
                $this->_productCollection = $this->_addProductAttributesAndPrices($this->collectionFactory->create())
                    ->addIdFilter($this->getIndexedIds())
                    ->setStore($this->_storeManager->getStore()->getId());
                $this->stockHelper->addIsInStockFilterToCollection($this->_productCollection);
            }
        }

        return $this->_productCollection;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initializeProductCollection()
    {
        //Parent part without blocks and sorting initializing.
        $layer = $this->getLayer();
        $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }

        $collection = $layer->getProductCollection();
        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        //Custom part.
        $collection->clear();
        $collection->setPageSize($this->getLimit());
        foreach ($this->getSortOrders() as $attributeCode => $direction) {
            $collection->setOrder($attributeCode, $direction);
        }
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );
        return $collection;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts()
    {
        if ($this->products === null) {
            $this->products = $this->getResults();

            if ($this->getAddToCart()) {
                $productModel = $this->productFactory->create();
                foreach ($this->products as &$product) {
                    $productModel->setData($product['product_data']);
                    $product['cart_post_params'] = $this->getAddToCartPostParams($productModel);
                    $product['compare_post_params'] = $this->getCompareProductsPostParams($productModel);
                    $product['wishlist_post_params'] = $this->getAddToWishlistParams($productModel);
                }
            }

            if ($this->getQuery() && $this->getNumResults() !== null) {
                $this->getQuery()->saveNumResults($this->getNumResults());
            }
        }

        return $this->products;
    }

    /**
     * @param ProductModel $productModel
     * @return string
     */
    public function getCompareProductsPostParams(ProductModel $productModel): string
    {
        $currentComparePostParams = $this->_compareProduct->getPostDataParams($productModel);

        return $this->preparePostParams($currentComparePostParams);
    }

    /**
     * @param ProductModel $product
     * @return string
     */
    public function getAddToWishlistParams($product): string
    {
        $postParams = parent::getAddToWishlistParams($product);

        return $this->preparePostParams($postParams);
    }

    private function preparePostParams(string $postParams): string
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($this->redirector->getRefererUrl());

        return str_replace($currentUenc, $newUenc, $postParams);
    }

    /**
     * @return array[]
     */
    public function getResults()
    {
        $query = $this->getQuery();
        $result = $query ? $this->searchAdapterResolver->getResults($this->getBlockType(), $query) : null;

        if ($result !== null) {
            $this->setNumResults($result->getResultsCount());
            $searchResult = $result->getItems();
        } else {
            $searchResult = $this->getCollectionData();
        }

        return $searchResult;
    }

    /**
     * @param ProductModel $product
     * @param string $imageId
     * @param array $attributes
     * @return Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        $imageBlock = parent::getImage($product, $imageId, $attributes);
        $imageBlock->setTemplate(self::IMAGE_WITH_BORDERS_TEMPLATE);

        return $imageBlock;
    }

    private function getCollectionData(): array
    {
        $imageId = $this->getImageId();
        $this->setNumResults($this->getLoadedProductCollection()->getSize());

        foreach ($this->getLoadedProductCollection() as $product) {
            $data['img'] = $this->encodeMediaUrl(
                $this->getImage($product, $imageId)->toHtml()
            );
            $data['image_url'] = $this->getImage($product, $imageId)->getImageUrl();
            $data['url'] = $this->getRelativeLink($product->getProductUrl());
            $data['name'] = $this->getName($product);
            $data['description'] = $this->getDescription($product);
            $data['price'] = $this->getProductPrice($product);
            $data['min_price'] = $product->getData('min_price');
            $data['final_price'] = $product->getData('final_price');
            $data['is_salable'] = $product->isSaleable();
            $data['product_data'] = [
                'entity_id' => (string)$product->getId(),
                'request_path' => (string)$product->getRequestPath()
            ];
            if ($this->isNeedShowProductReviews()) {
                $data['reviews'] = $this->getReviewsSummaryHtml(
                    $product,
                    ReviewRendererInterface::SHORT_VIEW
                );
                $data['rating_summary'] = $product->getData('rating_summary');
            }
            $data['cart_post_params'] = $this->getAddToCartPostParams($product);
            $data['cart_label'] = $this->getCartLabel($product);
            $data['entity_id'] = (string)$product->getId();
            $data['options_data'] = $this->productOptionsProvider->getOptionsData($product) ?: [];

            if ($this->config->isShowSku()) {
                $data['sku'] = $this->highlight($product->getData(ProductInterface::SKU));
            }

            $results[$product->getId()] = $data;
        }

        return $results ?? [];
    }

    /**
     * @return string
     */
    private function getImageId()
    {
        return $this->isPortoTheme() ? self::PORTO_IMAGE_ID : self::IMAGE_ID;
    }

    /**
     * @return bool
     */
    private function isPortoTheme()
    {
        return $this->design->getDesignTheme()->getCode() == self::SMARTWAVE_PORTO_CODE;
    }

    /**
     * Encode media url on elasticsearch reindex process in order to correctly handle pub/index.php execution.
     *
     * @param string $html
     * @return string
     */
    private function encodeMediaUrl($html)
    {
        if ($this->getLimit() === 0) {
            $html = str_replace($this->getMediaUrl(), self::MEDIA_URL_PLACEHOLDER, $html);
        }

        return $html;
    }

    /**
     * @return string
     */
    private function getMediaUrl()
    {
        if ($this->mediaUrl === null) {
            $this->mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        }

        return $this->mediaUrl;
    }

    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        if ($this->getData('limit') === null) {
            $limit = (int)$this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_PRODUCT_LIMIT);
            $this->setData('limit', max(1, $limit));
        }

        return $this->getData('limit');
    }

    /**
     * @return \Magento\Search\Model\Query
     */
    public function getQuery()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY);
    }

    /**
     * @return string
     */
    public function getResultUrl()
    {
        $result = null;
        if ($this->getQuery()) {
            $result = $this->xSearchHelper->getResultUrl($this->getQuery()->getQueryText());
        }

        return $result;
    }

    /**
     * @param $text
     * @return string
     */
    public function highlight($text)
    {
        if ($this->getQuery()) {
            $text = $this->xSearchHelper->highlight($text, $this->getQuery()->getQueryText());
        }

        return $text;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_TITLE);
    }

    /**
     * @return int|null
     */
    public function getHiddenProductsCount(): ?int
    {
        $count = (int)$this->getNumResults() - (int)$this->getLimit();

        return $count > 0 ? $count : null;
    }

    /**
     * @param ProductModel $product
     * @return string
     */
    private function getName(ProductModel $product)
    {
        $nameLength = $this->getNameLength();
        $productNameStripped = $this->stripTags($product->getName(), null, true);
        $text =
            $nameLength && $this->string->strlen($productNameStripped) > $nameLength ?
            $this->string->substr($productNameStripped, 0, $this->getNameLength()) . '...'
            : $productNameStripped;
        return $this->highlight($text);
    }

    /**
     * @return int
     */
    private function getNameLength()
    {
        return (int)$this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_NAME_LENGTH);
    }

    /**
     * @param ProductModel $product
     * @return string
     */
    public function getDescription(ProductModel $product)
    {
        $descLength = $this->getDescLength();
        $productDescStripped = $this->stripTags($product->getShortDescription(), null, true);

        $text =
            $this->string->strlen($productDescStripped) > $descLength ?
            $this->string->substr($productDescStripped, 0, $descLength) . '...'
            : $productDescStripped;

        return $this->highlight($text);
    }

    /**
     * @inheritdoc
     */
    protected function getPriceRender()
    {
        return $this->_layout->createBlock(
            \Magento\Framework\Pricing\Render::class,
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

    /**
     * @param ProductModel $product
     * @return array
     */
    public function getAddToCartPostParams(ProductModel $product)
    {
        $result = parent::getAddToCartPostParams($product);
        $result['data']['return_url'] =  $this->redirector->getRefererUrl();

        return $result;
    }

    /**
     * @return string
     */
    public function getUlrEncodedParam()
    {
        return Action::PARAM_NAME_URL_ENCODED;
    }

    /**
     * @param array $product
     * @return bool
     */
    public function isShowDescription(array $product)
    {
        return $this->string->strlen($product['description']) > 0
            && $this->getDescLength() > 0;
    }

    /**
     * @return int
     */
    private function getDescLength()
    {
        return (int)$this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_DESC_LENGTH);
    }

    /**
     * @return bool
     */
    public function getReviews()
    {
        return (bool)$this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_REVIEWS) == '1' ? 1 : 0;
    }

    public function isNeedShowProductReviews(): bool
    {
        return $this->config->getShowProductReviews();
    }

    /**
     * @see \Amasty\HidePrice\Plugin\Xsearch\Block\Search\Product\HideAddToCart::aroundGetAddToCart
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAddToCart(array $product = []): bool
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_ADD_TO_CART) == '1';
    }

    public function getCartLabel(ProductModel $product): string
    {
        return __('Add to Cart')->render();
    }

    /**
     * @param string $url
     * @return string
     */
    private function getRelativeLink($url)
    {
        $baseUrl = $this->getBaseUrl();
        $baseUrlPosition = strpos($url, $baseUrl);
        if ($baseUrlPosition !== false) {
            return substr($url, strlen($baseUrl) - 1);
        }
        return preg_replace('#^[^/:]+://[^/]+#', '', $url);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getFullLink($url)
    {
        $url = $this->getRelativeLink($url);
        return rtrim($this->getBaseUrl(), '/') . $url;
    }

    /**
     * @see \Amasty\HidePrice\Plugin\Xsearch\Block\Search\Product\HideWishlist::aroundIsWishlistAllowed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isWishlistAllowed(array $productData = []): bool
    {
        return $this->wishlistHelper->isAllow();
    }

    /**
     * @see \Amasty\HidePrice\Plugin\Xsearch\Block\Search\Product\HideCompare::aroundIsCompareAllowed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isCompareAllowed(array $productData = []): bool
    {
        return true;
    }

    /**
     * @return array ['attributeCode' => 'direction', ...]
     */
    public function getSortOrders(): array
    {
        return ['relevance' => Select::SQL_DESC];
    }
}
