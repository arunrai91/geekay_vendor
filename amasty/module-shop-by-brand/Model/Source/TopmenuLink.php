<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Source;

class TopmenuLink implements \Magento\Framework\Option\ArrayInterface
{
    public const DISPLAY_FIRST = 1;
    public const DISPLAY_LAST = 2;

    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Display First')],
            ['value' => 2, 'label' => __('Display Last')]
        ];
    }

    public function toArray()
    {
        return [0 => __('No'), 1 => __('Display First'), 2 => __('Display Last')];
    }
}
