<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\ViewModel\Email;

use Amasty\Acart\Model\ConfigProvider;
use Amasty\Acart\Model\UrlManager;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class ProductViewModel implements ArgumentInterface
{
    public const FIRST_PARENT_PRODUCT = 0;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Configuration
     */
    private $productConfig;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var UrlManager
     */
    private $urlManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Type
     */
    private $bundleType;

    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Output
     */
    private $outputHelper;

    public function __construct(
        Data $dataHelper,
        Configuration $productConfig,
        PriceCurrencyInterface $priceCurrency,
        Image $imageHelper,
        UrlManager $urlManager,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        Type $bundleType,
        Grouped $groupedType,
        ConfigProvider $configProvider,
        Output $outputHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->productConfig = $productConfig;
        $this->priceCurrency = $priceCurrency;
        $this->imageHelper = $imageHelper;
        $this->urlManager = $urlManager;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->groupedType = $groupedType;
        $this->configProvider = $configProvider;
        $this->outputHelper = $outputHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Quote\Model\Quote $item
     * @paarm bool $showConfigurableImage
     * @return ProductInterface
     */
    public function getProduct($item, bool $showConfigurableImage = false)
    {
        $product = null;

        if ($item instanceof ProductInterface) {
            $product = $this->productRepository->getById($item->getId());
        } elseif ($item->getQuote()) {
            if ($showConfigurableImage && $item->getProductType() == 'configurable') {
                $product = $this->productRepository->get($item->getSku(), false, $item->getQuote()->getStoreId());
            } else {
                $product = $this->productRepository->getById(
                    $item->getProductId(),
                    false,
                    $item->getQuote()->getStoreId()
                );
            }
        } elseif ($item->getProduct()) {
            $product = $this->productRepository->getById($item->getProductId());
        } else {
            $product = $item->getProduct();
        }

        return $product;
    }

    public function getProductUrl(Product $item): string
    {
        if ($item->getRedirectUrl()) {
            return $item->getRedirectUrl();
        }

        $option = $item->getOptionByCode('product_type');

        if ($option) {
            $item = $option->getProduct();
        }

        if ($item->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            $parentProductIds = $this->getParentIdsByChild($item->getId());

            if (!empty($parentProductIds[self::FIRST_PARENT_PRODUCT])) {
                $item = $this->productRepository->getById($parentProductIds[self::FIRST_PARENT_PRODUCT]);
            }
        }

        return $this->urlManager->get($item->getUrlModel()->getUrl($item));
    }

    public function getProductImageUrl(): string
    {
        $imageHelper = $this->getProductImageHelper();

        $imageUrl = $imageHelper->getDefaultPlaceholderUrl();
        if ($imageHelper->getUrl() !== $imageUrl) {
            $imageUrl = $this->prepareProductImageUrl($imageHelper->getUrl());
        }

        return $imageUrl;
    }

    public function prepareProductImageUrl(string $imgUrl): string
    {
        if ($this->configProvider->getRemovePubFromImgUrl() && strpos($imgUrl, '/pub/media/') !== false) {
            $imgUrl = str_replace('/pub/', '/', $imgUrl);
        }

        return $imgUrl;
    }

    public function initProductImageHelper(?Quote $quote, Product $product, string $imageId): void
    {
        if ($quote) {
            foreach ($quote->getAllItems() as $item) {
                if ($item->getParentItemId() && $item->getParentItemId() == $product->getId()) {
                    $product = $item;
                    break;
                }
            }
        }

        $this->imageHelper->init($product, $imageId);
    }

    public function getProductImageHelper(): Image
    {
        return $this->imageHelper;
    }

    public function getProductOptions($item, $product): array
    {
        $preparedOptionsData = [];
        $optionsData = $item->getProduct()
            ? $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct())
            : [];

        if ($optionsData) {
            if (isset($optionsData['attributes_info'])) {
                $preparedOptionsData = array_merge($preparedOptionsData, $optionsData['attributes_info']);
            }

            if (isset($optionsData['options'])) {
                $preparedOptionsData = array_merge($preparedOptionsData, $optionsData['options']);
            }

            if (isset($optionsData['additional_options'])) {
                $preparedOptionsData = array_merge($preparedOptionsData, $optionsData['additional_options']);
            }
        }

        if (!$optionsData || !$preparedOptionsData) {
            foreach ($product->getCustomOptions() as $option) {
                if ($option->getData('label')) {
                    $preparedOptionsData[] = $option->getData();
                }
            }
        }

        return $preparedOptionsData;
    }

    /**
     * @param string|array $optionValue
     * @return array
     */
    public function getFormatedOptionValue($optionValue): array
    {
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];

        return $this->productConfig->getFormattedOptionValue($optionValue, $params);
    }

    public function formatPrice(?Quote $quote, float $price): string
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $quote ? $quote->getStore() : null,
            $quote ? $quote->getCurrency()->getQuoteCurrencyCode() : null
        );
    }

    /**
     * @param Quote|null $quote
     * @param Product|Item $item
     * @param Product $product
     * @param bool $showPriceIncTax
     * @return string
     */
    public function getPrice(?Quote $quote, $item, Product $product, bool $showPriceIncTax): string
    {
        if ($showPriceIncTax) {
            $price = $this->getPriceIncTax($item, $product);
        } else {
            $price = $this->getPriceExcTax($item, $product);
        }

        return $this->formatPrice($quote, $price);
    }

    /**
     * @param Product|Item $item
     * @param Product $product
     * @return float
     */
    private function getPriceIncTax($item, Product $product): float
    {
        if ($item instanceof ProductInterface) {
            $price = $this->dataHelper->getTaxPrice($product, $product->getFinalPrice(), true);
        } else {
            $price = $item->getBasePriceInclTax();
        }

        return (float)$price;
    }

    /**
     * @param Product|Item $item
     * @param Product $product
     * @return float
     */
    private function getPriceExcTax($item, Product $product): float
    {
        if ($item instanceof ProductInterface) {
            $price = $product->getPrice();
        } else {
            $price = $item->getPrice();
        }

        return (float)$price;
    }

    private function getParentIdsByChild($itemId): array
    {
        $parentProductIds = [];

        if ($this->configurableType->getParentIdsByChild($itemId)) {
            $parentProductIds = $this->configurableType->getParentIdsByChild($itemId);
        } elseif ($this->bundleType->getParentIdsByChild($itemId)) {
            $parentProductIds = $this->bundleType->getParentIdsByChild($itemId);
        } elseif ($this->groupedType->getParentIdsByChild($itemId)) {
            $parentProductIds = $this->groupedType->getParentIdsByChild($itemId);
        }

        return $parentProductIds;
    }

    /**
     * @return Output
     */
    public function getOutputHelper(): Output
    {
        return $this->outputHelper;
    }
}
