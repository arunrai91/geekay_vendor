<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

class SortOptionsBy implements \Magento\Framework\Data\OptionSourceInterface
{
    public const POSITION = 0;
    public const NAME = 1;
    public const PRODUCT_COUNT = 2;

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::POSITION,
                'label' => __('Position')
            ],
            [
                'value' => self::NAME,
                'label' => __('Name')
            ],
            [
                'value' => self::PRODUCT_COUNT,
                'label' => __('Product Quantities')
            ],
        ];
    }
}
