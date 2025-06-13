<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Brand\ListDataProvider;

use Amasty\ShopbyBrand\Model\Brand\BrandDataInterface;

class FilterItems
{
    public const FOR_WIDGET = 'for_widget';

    public const FOR_SLIDER = 'for_slider';

    public const NOT_EMPTY = 'not_empty';

    /**
     * Amasty_ShopByBrandWidget
     */
    public const HIDED_BRANDS = 'hided_brands';

    /**
     * Amasty_ShopByBrandWidget
     */
    public const VISIBLE_BRANDS = 'visible_brands';

    /**
     * @param BrandDataInterface[] $items
     * @param array $filterParams
     *
     * @return BrandDataInterface[]
     */
    public function execute(array $items, array $filterParams): array
    {
        foreach ($items as $key => $item) {
            if (!empty($filterParams[self::FOR_WIDGET]) && !$item->getIsShowInWidget()) {
                unset($items[$key]);
                continue;
            }
            if (!empty($filterParams[self::FOR_SLIDER]) && !$item->getIsShowInSlider()) {
                unset($items[$key]);
                continue;
            }
            if (!empty($filterParams[self::NOT_EMPTY]) && !$item->getCount()) {
                unset($items[$key]);
                continue;
            }
            if (!empty($filterParams[self::HIDED_BRANDS])) {
                $hidedBrands = explode(',', $filterParams[self::HIDED_BRANDS]);
                if (in_array($item->getBrandId(), $hidedBrands, false)) {
                    unset($items[$key]);
                    continue;
                }
            }
            if (isset($filterParams[self::VISIBLE_BRANDS])) {
                $visibleBrands = $filterParams[self::VISIBLE_BRANDS];
                if (!is_array($visibleBrands)) {
                    $visibleBrands = explode(',', $visibleBrands);
                }
                if (!in_array($item->getBrandId(), $visibleBrands)) {
                    unset($items[$key]);
                    continue;
                }
            }
        }

        return $items;
    }
}
