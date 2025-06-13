<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RefreshIpBehaviour implements OptionSourceInterface
{
    public const MANUALLY = 0;
    public const VIA_AMASTY_SERVICE = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::MANUALLY, 'label' => __('Manually')],
            ['value' => self::VIA_AMASTY_SERVICE, 'label' => __('Update via Amasty Service')]
        ];
    }
}
