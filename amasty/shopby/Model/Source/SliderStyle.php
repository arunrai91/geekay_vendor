<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

class SliderStyle implements \Magento\Framework\Option\ArrayInterface
{
    public const DEFAULT = '-default';

    public const IMPROVED = '-improved';

    public const VOLUMETRIC_GRADIENT = '-volumetric';

    public const LIGHT = '-light';

    public const DARK = '-dark';

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
            self::DEFAULT => __('Default'),
            self::IMPROVED => __('Improved'),
            self::VOLUMETRIC_GRADIENT => __('Volumetric Gradient'),
            self::LIGHT => __('Light'),
            self::DARK => __('Dark')
        ];
    }
}
