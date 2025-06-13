<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    public const REFRESH_IP_BEHAVIOR = 'refresh_ip_database/behaviour';

    /**
     * @var string
     */
    protected $pathPrefix = 'amgeoip/';

    public function getRefreshIpBehaviour(): int
    {
        return (int)$this->getValue(self::REFRESH_IP_BEHAVIOR);
    }
}
