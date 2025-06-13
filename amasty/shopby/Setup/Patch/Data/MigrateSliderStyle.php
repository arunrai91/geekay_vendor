<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Setup\Patch\Data;

use Amasty\Shopby\Model\ConfigProvider;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateSliderStyle implements DataPatchInterface
{
    public const TABLE_CORE_CONFIG_DATA = 'core_config_data';

    public const OLD_SLIDER_STYLE_CONFIG_PATH = 'amshopby/general/slider_style';

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(
        ConfigInterface $resourceConfig
    ) {
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function apply()
    {
        $this->resourceConfig->getConnection()->update(
            $this->resourceConfig->getTable(self::TABLE_CORE_CONFIG_DATA),
            ['path' => 'amshopby/' . ConfigProvider::SLIDER_STYLE],
            ['path' . ' = (?)' => self::OLD_SLIDER_STYLE_CONFIG_PATH]
        );

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
