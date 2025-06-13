<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Source\Canonical;

class Category implements \Magento\Framework\Option\ArrayInterface
{
    public const CATEGORY_PURE = 'category_pure';

    public const CATEGORY_CURRENT = 'category_current';

    public const CATEGORY_CUT_OFF_GET = 'category_cut_off_get';

    public const CATEGORY_BRAND_FILTER = 'category_brand_filter';

    public const CATEGORY_FIRST_ATTRIBUTE = 'category_first_attribute';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $optionValue => $optionLabel) {
            $options[] = [
                'value' => $optionValue,
                'label' => $optionLabel
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::CATEGORY_CURRENT => __('Keep current URL'),
            self::CATEGORY_PURE => __('URL Without Filters'),
            self::CATEGORY_BRAND_FILTER => __('Brand Filter Only'),
            self::CATEGORY_FIRST_ATTRIBUTE => __('First Attribute Value'),
            self::CATEGORY_CUT_OFF_GET => __('Current URL without Get parameters')
        ];
    }
}
