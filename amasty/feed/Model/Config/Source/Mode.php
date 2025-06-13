<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    public const MANUALLY = 'manual';
    public const HOURLY = 'hourly';
    public const DAILY = 'daily';
    public const WEEKLY = 'weekly';
    public const MONTHLY = 'monthly';

    public function toOptionArray(): array
    {
        return [
            self::MANUALLY => __('Manually'),
            self::HOURLY => __('Hourly'),
            self::DAILY => __('Daily'),
            self::WEEKLY => __('Weekly'),
            self::MONTHLY => __('Monthly'),
        ];
    }
}
