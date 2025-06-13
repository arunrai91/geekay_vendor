<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\OptionSettings;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Framework\UrlInterface;

class UrlResolver
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function resolveBrandUrl(OptionSettingInterface $optionSetting): string
    {
        $brandCode = $optionSetting->getFilterCode();
        if (!$brandCode) {
            return $this->urlBuilder->getBaseUrl();
        }

        return $this->urlBuilder->getUrl('amshopby/index/index', [
            '_query' => [$brandCode => $optionSetting->getOptionId()],
        ]);
    }

    /**
     * Get full URL path for brand image.
     */
    public function resolveImageUrl(OptionSettingInterface $optionSetting): ?string
    {
        if (!$optionSetting->getImage()) {
            return null;
        }

        return rtrim($this->getMediaBaseUrl(), '/') . OptionSetting::IMAGES_DIR . $optionSetting->getImage();
    }

    /**
     * Get full URL path for brand slider image.
     */
    public function resolveSliderImageUrl(OptionSettingInterface $optionSetting, bool $strict = false): ?string
    {
        if (!$optionSetting->getSliderImage()) {
            return $strict ? null : $this->resolveImageUrl($optionSetting);
        }

        return rtrim($this->getMediaBaseUrl(), '/')  . OptionSetting::IMAGES_DIR . OptionSetting::SLIDER_DIR
            . $optionSetting->getSliderImage();
    }

    public function getMediaBaseUrl(): string
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
    }
}
