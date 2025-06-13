<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\ViewModel;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Model\AllProductsConfig;
use Amasty\ShopbyBase\Model\OptionSetting;

class OptionProcessor implements OptionProcessorInterface
{
    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var AllProductsConfig
     */
    private $allProductsConfig;

    public function __construct(UrlBuilderInterface $urlBuilder, AllProductsConfig $allProductsConfig)
    {
        $this->urlBuilder = $urlBuilder;
        $this->allProductsConfig = $allProductsConfig;
    }

    public function process(OptionSetting $setting): array
    {
        $label = $setting->getAttributeOption()->getLabel();
        $title = $label ?: $setting->getTitle();

        return [
            self::IMAGE_URL => $setting->getSliderImageUrl(),
            self::LINK_URL => $this->getOptionSettingUrl($setting),
            self::TITLE => $title,
            OptionSetting::SMALL_IMAGE_ALT => $setting->getSmallImageAlt()
        ];
    }

    /**
     * @param OptionSetting $setting
     * @return string
     */
    private function getOptionSettingUrl(OptionSetting $setting): string
    {
        $attributeCode = $setting->getAttributeCode();
        if (!$attributeCode) {
            return $this->urlBuilder->getBaseUrl();
        }

        if (!$this->allProductsConfig->isAllProductsAvailable()) {
            return '#';
        }

        $value = $setting->getOptionId() ?: $setting->getValue();

        return $this->urlBuilder->getUrl(
            'amshopby/index/index',
            [
                '_query' => [$attributeCode => $value],
            ]
        );
    }
}
