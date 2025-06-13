<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Source;

class ShowProductQuantities implements \Magento\Framework\Option\ArrayInterface
{
    public const SHOW_DEFAULT = 0;
    public const SHOW_YES = 1;
    public const SHOW_NO = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SHOW_DEFAULT,
                'label' => __('Default')
            ],
            [
                'value' => self::SHOW_YES,
                'label' => __('Yes')
            ],
            [
                'value' => self::SHOW_NO,
                'label' => __('No')
            ],
        ];
    }
}
