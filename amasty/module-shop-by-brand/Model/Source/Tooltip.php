<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Source;

class Tooltip implements \Magento\Framework\Option\ArrayInterface
{
    public const NO_DISPLAY = 'no';
    public const ALL_BRAND_PAGE = 'all_brands';
    public const PRODUCT_PAGE = 'product_page';
    public const LISTING_PAGE = 'listing_page';

    public function toOptionArray()
    {
        return [
            ['value' => self::NO_DISPLAY, 'label' => __('No')],
            ['value' => self::ALL_BRAND_PAGE, 'label' => __('All Brands page')],
            ['value' => self::PRODUCT_PAGE, 'label' => __('Product page')],
            ['value' => self::LISTING_PAGE, 'label' => __('Listing page')],
        ];
    }
}
