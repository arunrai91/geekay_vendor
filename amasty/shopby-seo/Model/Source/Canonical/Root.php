<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Source\Canonical;

class Root implements \Magento\Framework\Option\ArrayInterface
{
    public const ROOT_CURRENT = 'root_current';

    public const ROOT_FIRST_ATTRIBUTE = 'root_first_attribute';

    public const ROOT_PURE = 'root_pure';

    public const ROOT_CUT_OFF_GET = 'root_cut_off_get';

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
            self::ROOT_CURRENT => __('Keep current URL'),
            self::ROOT_PURE => __('URL Key Only'),
            self::ROOT_FIRST_ATTRIBUTE => __('First Attribute Value'),
            self::ROOT_CUT_OFF_GET => __('Current URL without Get parameters')
        ];
    }
}
