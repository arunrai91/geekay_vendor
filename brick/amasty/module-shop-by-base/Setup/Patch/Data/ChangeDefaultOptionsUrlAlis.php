<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Setup\Patch\Data;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Improve use default functionality for options URL alias.
 */
class ChangeDefaultOptionsUrlAlis implements DataPatchInterface
{
    /**
     * @var OptionSetting
     */
    private $optionSettingResource;

    public function __construct(
        OptionSetting $optionSettingResource
    ) {
        $this->optionSettingResource = $optionSettingResource;
    }

    /**
     * Replace empty string values with NULL.
     * For now on, values with empty string have different functionality.
     *
     * @return $this
     */
    public function apply()
    {
        $connection = $this->optionSettingResource->getConnection();

        $connection->update(
            $this->optionSettingResource->getMainTable(),
            [OptionSettingInterface::URL_ALIAS => null],
            OptionSettingInterface::URL_ALIAS . " = ''"
        );

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
