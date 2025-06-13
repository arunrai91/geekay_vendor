<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Adapter;

use Amasty\Xsearch\Block\Search\Product as ProductBlock;
use Amasty\Xsearch\Model\Config;
use Amasty\Xsearch\Model\SharedCatalog\Resolver;
use Amasty\Xsearch\Model\SharedCatalog\SharedCatalog;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Builder as RequestBuilder;
use Magento\Framework\Search\Request\BuilderFactory;
use Magento\Framework\UrlInterface;
use Magento\Search\Model\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;

class Product
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var null|Resolver
     */
    private $sharedCatalog;

    /**
     * @var BuilderFactory
     */
    private $requestBuilderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        Config $config,
        BuilderFactory $requestBuilderFactory,
        StoreManagerInterface $storeManager,
        AdapterFactory $adapterFactory,
        SharedCatalog $sharedCatalog,
        SortOrderBuilder $sortOrderBuilder = null
    ) {
        $this->config = $config;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->sharedCatalog = $sharedCatalog->get();
        $this->storeManager = $storeManager;
        $this->adapterFactory = $adapterFactory;
        $this->sortOrderBuilder = $sortOrderBuilder ?? ObjectManager::getInstance()->get(SortOrderBuilder::class);
    }

    public function getProductIndex(string $searchQuery, string $type): array
    {
        $limit = (int) $this->config->getModuleConfig($type . '/limit');
        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuilderFactory->create();
        $scope = $this->storeManager->getStore()->getId();
        $requestBuilder->bindDimension('scope', $scope);
        $requestBuilder->setRequestName('quick_search_container');
        $requestBuilder->bind('visibility', [3, 4]);
        $requestBuilder->bind('search_term', $searchQuery);
        if (!$this->sharedCatalog) {
            $requestBuilder->setSize($limit);
        }
        $requestBuilder->setSort($this->convertSortOrders($this->getSortOrders()));
        $request = $requestBuilder->create();
        $searchResponse = $this->adapterFactory->create()->queryAdvancedSearchProduct($request);
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        if ($this->sharedCatalog) {
            $searchResponse = $this->sharedCatalog->resolve($searchResponse);
        }
        foreach ($searchResponse['products'] as &$product) {
            if (!empty($product['img'])) {
                $product['img'] = str_replace(ProductBlock::MEDIA_URL_PLACEHOLDER, $mediaUrl, $product['img']);
            }
        }

        return $searchResponse;
    }

    public function sortProducts(array $products): array
    {
        if ($this->config->isShowOutOfStockLast()) {
            $outOfStockProducts = [];
            foreach ($products as $key => $product) {
                if (!$product['is_salable']) {
                    $outOfStockProducts[$key] = $product;
                    unset($products[$key]);
                }
            }

            $products = array_replace($products, $outOfStockProducts);
        }

        return $products;
    }

    /**
     * @return array ['attributeCode' => 'direction', ...]
     */
    public function getSortOrders(): array
    {
        return [];
    }

    /**
     * @param array $sortOrders ['attributeCode' => 'direction', ...]
     * @return SortOrder[]
     */
    private function convertSortOrders(array $sortOrders): array
    {
        $result = [];
        foreach ($sortOrders as $attributeCode => $direction) {
            $this->sortOrderBuilder->setField($attributeCode);
            $this->sortOrderBuilder->setDirection($direction);
            $result[] = $this->sortOrderBuilder->create();
        }

        return $result;
    }
}
