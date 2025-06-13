<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Api;

use Amasty\Xsearch\Api\Data\ProductRenderInterface;
use Amasty\Xsearch\Api\Data\ProductRenderInterfaceFactory;
use Amasty\Xsearch\Api\Data\SearchResultInterface;
use Amasty\Xsearch\Api\Data\SearchResultInterfaceFactory;
use Amasty\Xsearch\Api\SearchInterface;
use Amasty\Xsearch\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorComposite;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FulltextCollection;
use Magento\Framework\Data\CollectionModifier;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class Search implements SearchInterface
{
    private const DEFAULT_PAGE = 1;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var CollectionModifier
     */
    private $collectionModifier;

    /**
     * @var ProductRenderInterfaceFactory
     */
    private $productRenderFactory;

    /**
     * @var PriceInfoInterfaceFactory
     */
    private $priceInfoFactory;

    /**
     * @var ProductRenderCollectorComposite
     */
    private $productRenderCollectorComposite;

    /**
     * @var SearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        LayerResolver $layerResolver,
        CollectionModifier $collectionModifier,
        ProductRenderInterfaceFactory $productRenderFactory,
        PriceInfoInterfaceFactory $priceInfoFactory,
        ProductRenderCollectorComposite $productRenderCollectorComposite,
        SearchResultInterfaceFactory $searchResultFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->layerResolver = $layerResolver;
        $this->collectionModifier = $collectionModifier;
        $this->productRenderFactory = $productRenderFactory;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->productRenderCollectorComposite = $productRenderCollectorComposite;
        $this->searchResultFactory = $searchResultFactory;
        $this->priceCurrency = $priceCurrency;
    }

    public function search(
        string $query,
        int $storeId,
        string $currencyCode,
        ?int $page = null,
        ?int $pageSize = null
    ): SearchResultInterface {
        $page = $page ?? self::DEFAULT_PAGE;
        $pageSize = $pageSize ?? $this->config->getLimit($storeId);

        $this->storeManager->setCurrentStore($storeId);
        $this->storeManager->getStore()->setData(
            'current_currency',
            $this->priceCurrency->getCurrency(null, $currencyCode)
        ); // set current currency instead of load from http context

        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);

        /** @var FulltextCollection $fulltextCollection */
        $fulltextCollection = $this->layerResolver->get()->getProductCollection();
        $fulltextCollection->setStoreId($storeId);
        $fulltextCollection->addSearchFilter($query);
        $fulltextCollection->setPage($page, $pageSize);
        $fulltextCollection->setOrder('relevance');
        $this->collectionModifier->apply($fulltextCollection);

        $items = [];
        foreach ($fulltextCollection as $item) {
            /** @var ProductRenderInterface $productRenderInfo */
            $productRenderInfo = $this->productRenderFactory->create();
            $productRenderInfo->setSku($item->getSku());
            $productRenderInfo->setStoreId($storeId);
            $productRenderInfo->setCurrencyCode($currencyCode);
            $this->updateSpecialPrice($item, $productRenderInfo);
            $this->productRenderCollectorComposite->collect($item, $productRenderInfo);
            $items[$item->getId()] = $productRenderInfo;
        }

        $searchResult = $this->searchResultFactory->create();
        $searchResult->setProducts($items);
        $searchResult->setProductTotalCount($fulltextCollection->getSize());
        $searchResult->setProductLastPage($fulltextCollection->getLastPageNumber());

        return $searchResult;
    }

    private function updateSpecialPrice(ProductInterface $product, ProductRenderInterface $productRenderInfo): void
    {
        if (!$productRenderInfo->getPriceInfo()) {
            $productRenderInfo->setPriceInfo($this->priceInfoFactory->create());
        }

        $specialPrice = $product->getPriceInfo()
            ->getPrice('special_price')
            ->getAmount()
            ->getValue();
        $productRenderInfo->getPriceInfo()->setSpecialPrice($specialPrice !== false ? $specialPrice : null);
    }
}
