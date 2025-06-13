<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source\Attribute;

class Extended extends \Amasty\Shopby\Model\Source\Attribute
{
    public const ALL = 'amshopby_all_attributes';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray($boolean = 1)
    {
        $allOption = [[
            'value' => self::ALL,
            'label' => (string)(__('All Attributes'))
        ]];
        return array_merge($allOption, parent::toOptionArray());
    }
}
