<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

class RenderCategoriesLevel implements \Magento\Framework\Option\ArrayInterface
{
    public const ROOT_CATEGORY = 1;
    public const CURRENT_CATEGORY_LEVEL = 2;
    public const CURRENT_CATEGORY_CHILDREN = 3;

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ROOT_CATEGORY,
                'label' => __('Root Category')
            ],
            [
                'value' => self::CURRENT_CATEGORY_LEVEL,
                'label' => __('Current Category Level')
            ],
            [
                'value' => self::CURRENT_CATEGORY_CHILDREN,
                'label' => __('Current Category Children')
            ],
        ];
    }
}
