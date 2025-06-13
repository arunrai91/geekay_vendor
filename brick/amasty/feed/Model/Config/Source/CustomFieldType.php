<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomFieldType implements OptionSourceInterface
{
    public const ATTRIBUTE = 0;
    public const CUSTOM_TEXT = 1;
    public const MERGED_ATTRIBUTES = 2;

    public function toOptionArray(): array
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    public function toArray(): array
    {
        return [
            self::ATTRIBUTE => __('Attribute'),
            self::CUSTOM_TEXT => __('Custom Text'),
            self::MERGED_ATTRIBUTES => __('Merged Attributes')
        ];
    }
}
