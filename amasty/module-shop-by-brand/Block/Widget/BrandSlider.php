<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBrand\Model\Brand\ListDataProvider\FilterItems;
use Amasty\ShopbyBrand\Model\Source\SliderSort;
use Magento\Widget\Block\BlockInterface;

class BrandSlider extends BrandListAbstract implements BlockInterface
{
    public const HTML_ID = 'amslider_id';

    public const DEFAULT_ITEM_NUMBER = 4;

    public const DEFAULT_IMG_WIDTH = 130;

    /**
     * deprecated. used for back compatibility.
     */
    public const CONFIG_VALUES_PATH = 'amshopby_brand/slider';

    /**
     * @var  array|null
     */
    protected $items;

    public function getCacheKeyInfo()
    {
        $parts = parent::getCacheKeyInfo();
        $parts[] = 'brand_slider_widget';
        $parts['slidesPerView'] = $this->getSlidesPerView();
        $parts['loop'] = $this->getLoop();
        $parts['simulateTouch'] = $this->getSimulateTouch();
        $parts['autoplay'] = $this->getAutoplayTime();
        $parts['pagination'] = $this->isPaginationShow();
        $parts['displayed_brands'] = $this->geHideBrands();

        return $parts;
    }

    /**
     * Because of nature of how the loop mode works, total number of slides must be >= slidesPerView * 2
     *
     * @see https://swiperjs.com/swiper-api#param-loop
     * @see https://swiperjs.com/migration-guide-v9#loop-mode
     */
    public function isLoopAvailable(int $slidesPerView): bool
    {
        return $this->getLoop() && $slidesPerView * 2 <= count($this->getItems());
    }

    public function getSlidesPerView(): int
    {
        return max(1, $this->getItemNumber());
    }

    public function getLoop(): int
    {
        return (int) $this->getData('infinity_loop');
    }

    public function getSimulateTouch(): int
    {
        return (int) $this->getData('simulate_touch');
    }

    public function isPaginationShow(): bool
    {
        return (bool) $this->getData('pagination_show');
    }

    public function isAutoplay(): bool
    {
        return (bool) $this->getData('autoplay');
    }

    public function getAutoplayTime(): int
    {
        return (int) $this->getData('autoplay_delay');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!count($this->getItems())) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if ($this->items === null) {
            $storeId = (int) $this->_storeManager->getStore()->getId();
            $this->items = $this->brandListDataProvider->getList(
                $storeId,
                $this->getItemsFilter(),
                $this->getData('sort_by') ?? SliderSort::NAME
            );
        }

        return $this->items;
    }

    public function getItemsFilter(): array
    {
        $filters = [
            FilterItems::FOR_SLIDER => true
        ];

        if (!$this->isDisplayZero()) {
            $filters[FilterItems::NOT_EMPTY] = true;
        }

        $filters[FilterItems::HIDED_BRANDS] = $this->geHideBrands();

        return $filters;
    }

    public function getHeaderColor(): string
    {
        return (string) $this->getData('slider_header_color');
    }

    public function getTitleColor(): string
    {
        return (string) $this->getData('slider_title_color');
    }

    public function getTitle(): string
    {
        return (string) $this->getData('slider_title');
    }

    public function getItemNumber(): int
    {
        return (int) $this->getData('items_number') ?: self::DEFAULT_ITEM_NUMBER;
    }

    public function isSliderEnabled(): bool
    {
        return count($this->getItems()) > $this->getItemNumber();
    }

    public function geHideBrands(): string
    {
        return (string)$this->getData(FilterItems::HIDED_BRANDS);
    }

    protected function getConfigValuesPath(): string
    {
        return self::CONFIG_VALUES_PATH;
    }
}
