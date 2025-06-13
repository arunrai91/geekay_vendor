<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

class FilterPlacedBlock implements \Magento\Framework\Option\ArrayInterface
{
    public const POSITION_SIDEBAR = 0;
    public const POSITION_TOP = 1;
    public const POSITION_BOTH = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION_SIDEBAR,
                'label' => __('Sidebar')
            ],
            [
                'value' => self::POSITION_TOP,
                'label' => __('Top')
            ],
            [
                'value' => self::POSITION_BOTH,
                'label' => __('Both')
            ]
        ];
    }
}
