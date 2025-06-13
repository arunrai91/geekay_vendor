<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ParentPriority implements OptionSourceInterface
{
    public const CONFIGURABLE  = 'configurable';
    public const BUNDLE  = 'bundle';

    public function toOptionArray(): array
    {
        return [
            '' => __('Please Select…'),
            self::CONFIGURABLE => __('Configurable'),
            self::BUNDLE => __('Bundle')
        ];
    }
}
