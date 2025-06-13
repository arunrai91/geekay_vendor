<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RangeAlgorithm implements OptionSourceInterface
{
    public const DEFAULT = 0;
    public const CUSTOM = 1;

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::DEFAULT,
                'label' => __('Default system algorithm')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom algorithm')
            ]
        ];
    }
}
