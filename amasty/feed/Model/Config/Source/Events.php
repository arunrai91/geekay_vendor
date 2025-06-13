<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Events implements OptionSourceInterface
{
    public const SUCCESS = 'success';
    public const UNSUCCESS = 'unsuccess';
    public const NONE = 'none';

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
            self::NONE => __('None'),
            self::SUCCESS => __('Successful Export'),
            self::UNSUCCESS => __('Unsuccessful Export')
        ];
    }
}
