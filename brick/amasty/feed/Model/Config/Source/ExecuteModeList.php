<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExecuteModeList implements OptionSourceInterface
{
    public const CRON = 'schedule';
    public const MANUAL = 'manual';
    public const CRON_GENERATED = 'By Schedule';
    public const MANUAL_GENERATED = 'Manually';

    public function toOptionArray(): array
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    public function toArray(): array
    {
        return [
            self::MANUAL => __('Manually'),
            self::CRON => __('By Schedule'),
        ];
    }
}
