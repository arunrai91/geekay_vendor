<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Setup\Patch\Data;

use Amasty\Rules\Api\Data\RuleInterface;
use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateSkipItemsSetting implements DataPatchInterface
{
    private const SKIP_SETTING_AS_DEFAULT = 0;
    private const SKIP_SETTING_NO = 2;
    private const ENABLE_GENERAL_SKIP_SETTINGS = 1;
    private const SKIP_SETTINGS_TO_UPDATE = [self::SKIP_SETTING_AS_DEFAULT, self::SKIP_SETTING_NO];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->update(
            $this->moduleDataSetup->getTable(Rule::TABLE_NAME),
            [
                RuleInterface::KEY_SKIP_RULE => '',
                RuleInterface::KEY_GENERAL_SKIP_SETTINGS => self::ENABLE_GENERAL_SKIP_SETTINGS
            ],
            [RuleInterface::KEY_SKIP_RULE . ' IN (?)' => self::SKIP_SETTINGS_TO_UPDATE]
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
