<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup\Patch\Data;

use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Amasty\AdminActionsLog\Setup\Operation\TableDataMigrator;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateActiveSessionsData implements DataPatchInterface
{
    public const OLD_TABLE_NAME = 'amasty_audit_active';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var TableDataMigrator
     */
    private $tableDataMigrator;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        TableDataMigrator $tableDataMigrator
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->tableDataMigrator = $tableDataMigrator;
    }

    public function apply(): MigrateActiveSessionsData
    {
        $this->moduleDataSetup->startSetup();
        $this->tableDataMigrator->migrateData(
            $this->moduleDataSetup,
            self::OLD_TABLE_NAME,
            ActiveSessionResource::TABLE_NAME,
            [
                'date_time' => 'session_start',
                'name' => 'full_name'
            ]
        );
        $this->moduleDataSetup->endSetup();

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
