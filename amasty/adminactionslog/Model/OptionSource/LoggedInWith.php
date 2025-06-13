<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\OptionSource;

class LoggedInWith implements \Magento\Framework\Data\OptionSourceInterface
{
    public const NEW_DEVICE = 'new_device';
    public const NEW_IP = 'new_ip';
    public const NEW_LOCATION = 'new_location';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::NEW_DEVICE => __('From New Device'),
            self::NEW_IP => __('With New IP Address'),
            self::NEW_LOCATION => __('From New Location')
        ];
    }
}
