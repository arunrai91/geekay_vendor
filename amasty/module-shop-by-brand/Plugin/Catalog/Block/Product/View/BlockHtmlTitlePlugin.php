<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\View;

use Amasty\ShopbyBase\Block\Product\AttributeIcon;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbyBrand\Model\Source\Tooltip;
use Amasty\ShopbyBrand\ViewModel\OptionProcessor;
use Magento\Framework\View\Element\BlockFactory;

class BlockHtmlTitlePlugin
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OptionProcessor
     */
    private $optionProcessor;

    public function __construct(
        BlockFactory $blockFactory,
        ConfigProvider $configProvider,
        OptionProcessor $optionProcessor
    ) {
        $this->blockFactory = $blockFactory;
        $this->configProvider = $configProvider;
        $this->optionProcessor = $optionProcessor;
    }

    /**
     * Add Brand Label to Product Page
     *
     * @param \Magento\Theme\Block\Html\Title $original
     * @param $html
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToHtml($original, $html)
    {
        if ($this->isShowLogo()
            || $this->configProvider->isDisplayTitle()
            || $this->configProvider->isDisplayDescription()
        ) {
            $logoHtml = $this->generateLogoHtml();

            $html = str_replace('/h1>', '/h1>' . $logoHtml, $html);
        }

        return $html;
    }

    /**
     * @return string
     */
    private function generateLogoHtml(): string
    {
        $this->optionProcessor->setPageType(Tooltip::PRODUCT_PAGE);

        /** @var AttributeIcon $block */
        $block = $this->blockFactory->createBlock(
            AttributeIcon::class,
            [
                'data' => [
                    AttributeIcon::PAGE_TYPE => 'product',
                    AttributeIcon::KEY_ATTRIBUTE_CODES => $this->getAttributeCodes(),
                    AttributeIcon::KEY_OPTION_PROCESSOR => $this->optionProcessor,
                ]
            ]
        );

        return $block->toHtml();
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
    private function isShowLogo(): bool
    {
        return $this->configProvider->isDisplayBrandImage();
    }
}
