<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\ViewModel;

use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\Resizer;
use Amasty\ShopbyBase\ViewModel\OptionProcessorInterface;
use Amasty\ShopbyBrand\Helper\Data;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopbyBrand\Model\Source\Tooltip;

class OptionProcessor implements OptionProcessorInterface
{
    /**
     * @var string
     */
    private $pageType = Tooltip::PRODUCT_PAGE;

    /**
     * @var Resizer
     */
    private $resizer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $brandHelper;

    public function __construct(
        Resizer $resizer,
        ConfigProvider $configProvider,
        Data $brandHelper
    ) {
        $this->resizer = $resizer;
        $this->configProvider = $configProvider;
        $this->brandHelper = $brandHelper;
    }

    /**
     * @return string
     */
    public function getPageType(): string
    {
        return $this->pageType;
    }

    /**
     * @param $pageType
     */
    public function setPageType($pageType): void
    {
        $this->pageType = $pageType;
    }

    /**
     * @param OptionSetting $setting
     *
     * @return array
     */
    public function process(OptionSetting $setting): array
    {
        $title = $setting->getTitle() ?: $setting->getAttributeOption()->getLabel();
        $data = [
            self::LINK_URL => $this->getOptionSettingUrl($setting),
            self::TITLE => $title,
            self::DISPLAY_TITLE => $this->isDisplayTitle(),
            OptionSetting::SMALL_IMAGE_ALT => $setting->getSmallImageAlt()
        ];

        if ($this->isDisplayLogo()) {
            $data[self::IMAGE_URL] = $this->getProductPageLogoUrl($setting);
        }

        if ($this->isDisplayDescription()) {
            $data[self::SHORT_DESCRIPTION] = $setting->getShortDescription();
        }

        if ($this->isToolTipEnabled()) {
            $data[self::TOOLTIP_JS] = $this->getTooltipTemplate(
                [
                    'title' => $title,
                    'label' => $setting->getLabel(),
                    'img' => $setting->getSliderImageUrl(),
                    'image' => $setting->getImageUrl(),
                    'description' => $setting->getDescription(true),
                    'short_description' => $setting->getShortDescription(),
                ]
            );
        }

        return $data;
    }

    /**
     * @param OptionSetting $setting
     *
     * @return bool|string|null
     */
    private function getProductPageLogoUrl(OptionSetting $setting)
    {
        $url = $setting->getSliderImageUrl();

        if ($url
            && ($width = $this->getLogoWidth())
            && ($height = $this->getLogoHeight())
        ) {
            $url = $this->resizer->getImageUrl($url, $width, $height);
        }

        return $url;
    }

    /**
     * @param OptionSetting $setting
     *
     * @return string
     */
    private function getOptionSettingUrl(OptionSetting $setting)
    {
        $url = '';
        $option = $setting->getAttributeOption();
        if ($option) {
            $url = $this->brandHelper->getBrandUrl($option);
        }

        return $url;
    }

    /**
     * @return bool
     */
    private function isToolTipEnabled(): bool
    {
        return \in_array($this->getPageType(), $this->configProvider->getTooltipEnabled(), true);
    }

    /**
     * @param array $item
     *
     * @return array|string
     */
    private function getTooltipTemplate(array $item)
    {
        return $this->brandHelper->generateToolTipContent($item);
    }

    private function isDisplayDescription(): bool
    {
        if ($this->getPageType() !== Tooltip::LISTING_PAGE) {
            return $this->configProvider->isDisplayDescription();
        }

        return false;
    }

    private function isDisplayTitle(): bool
    {
        if ($this->getPageType() !== Tooltip::LISTING_PAGE) {
            return $this->configProvider->isDisplayTitle();
        }

        return false;
    }

    private function isDisplayLogo(): bool
    {
        if ($this->getPageType() === Tooltip::LISTING_PAGE) {
            return $this->configProvider->isShowOnListing();
        }

        return $this->configProvider->isDisplayBrandImage();
    }

    private function getLogoWidth(): int
    {
        if ($this->getPageType() === Tooltip::LISTING_PAGE) {
            return $this->configProvider->getListingBrandLogoWidth();
        }

        return $this->configProvider->getLogoWidth();
    }

    private function getLogoHeight(): int
    {
        if ($this->getPageType() === Tooltip::LISTING_PAGE) {
            return $this->configProvider->getListingBrandLogoHeight();
        }

        return $this->configProvider->getLogoHeight();
    }
}
