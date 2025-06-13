<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Source\Canonical;

use Magento\Framework\Data\OptionSourceInterface;

class Brand implements OptionSourceInterface
{
    public const BRAND_FIRST_ATTRIBUTE = 'brand_first_attribute';

    public const BRAND_PURE = 'brand_pure';

    public const BRAND_CURRENT = 'brand_current';

    public const BRAND_CUT_OFF_GET = 'brand_cut_off_get';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
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
    public function toArray(): array
    {
        return [
            self::BRAND_CURRENT => __('Keep current URL'),
            self::BRAND_PURE => __('URL Without Filters'),
            self::BRAND_FIRST_ATTRIBUTE => __('First Attribute Value'),
            self::BRAND_CUT_OFF_GET => __('Current URL without Get parameters')
        ];
    }
}
