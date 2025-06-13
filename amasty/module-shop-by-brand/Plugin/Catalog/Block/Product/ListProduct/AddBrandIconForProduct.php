<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\ListProduct;

use Amasty\Mostviewed\Block\Widget\Related;
use Amasty\ShopbyBase\Block\Product\AttributeIcon;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbyBrand\Model\Source\Tooltip;
use Amasty\ShopbyBrand\ViewModel\OptionProcessor;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\BlockFactory;

class AddBrandIconForProduct
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OptionProcessor
     */
    private $optionProcessor;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    public function __construct(
        ConfigProvider $configProvider,
        OptionProcessor $optionProcessor,
        BlockFactory $blockFactory
    ) {
        $this->configProvider = $configProvider;
        $this->optionProcessor = $optionProcessor;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @param ListProduct $original
     * @param string $html
     * @param Product $product
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductPrice(ListProduct $original, string $html, Product $product): string
    {
        $this->setProduct($product);

        return $html . $this->getLogoHtml();
    }

    /**
     * Add Brand Label to Amasty Related Block
     *
     * @param Related $original
     * @param $html
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetBrandLogoHtml(
        Related $original, // @phpstan-ignore class.notFound
        $html,
        Product $product
    ): string {
        $this->setProduct($product);

        return $html . $this->getLogoHtml();
    }

    /**
     * @return string
     */
    private function getLogoHtml(): string
    {
        $logoHtml = '';
        if ($this->isShowOnListing()) {
            $this->optionProcessor->setPageType(Tooltip::LISTING_PAGE);

            /** @var AttributeIcon $block */
            $block = $this->blockFactory->createBlock(
                AttributeIcon::class,
                [
                    'data' => [
                        AttributeIcon::KEY_ATTRIBUTE_CODES => $this->getAttributeCodes(),
                        AttributeIcon::KEY_OPTION_PROCESSOR => $this->optionProcessor,
                        AttributeIcon::KEY_PRODUCT => $this->getProduct(),
                        AttributeIcon::PAGE_TYPE => 'listing'
                    ]
                ]
            );

            $logoHtml = $block->toHtml();
        }

        return $logoHtml;
    }

    /**
     * @return array
     */
    private function getAttributeCodes(): array
    {
        if ($code = $this->configProvider->getBrandAttributeCode()) {
            return [$code];
        }

        return [];
    }

    /**
     * @return bool
     */
    private function isShowOnListing(): bool
    {
        return $this->configProvider->isShowOnListing();
    }

    /**
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    private function setProduct(Product $product): void
    {
        $this->product = $product;
    }
}
