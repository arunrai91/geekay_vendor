<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateSyncDbCronSchedule implements DataPatchInterface
{
    private const CONFIG_PATH = 'amgeoip/refresh_ip_database/cron_schedule';

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(
        ConfigInterface $resourceConfig
    ) {
        $this->resourceConfig = $resourceConfig;
    }

    public function apply(): self
    {
        $schedule = Random::getRandomNumber(0, 59) . ' ' . Random::getRandomNumber(0, 6) . ' * * *';

        $this->resourceConfig->saveConfig(self::CONFIG_PATH, $schedule);

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
