<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BrandsDisplay implements OptionSourceInterface
{
    public const DISPLAY_ZERO = 'display_zero';
    public const DISPLAY_RELATED_TO_CATEGORY = 'display_related_to_category';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::DISPLAY_ZERO,
                'label' => __('Show Brands without Products')
            ],
            [
                'value' => self::DISPLAY_RELATED_TO_CATEGORY,
                'label' => __('Show Brands Related to Category'),
                'disabled' => true
            ]
        ];
    }
}
