<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup\Patch\Data;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry as VisitHistoryEntryResource;
use Amasty\AdminActionsLog\Setup\Operation\TableDataMigrator;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateVisitEntryData implements DataPatchInterface
{
    public const OLD_TABLE_NAME = 'amasty_audit_visit';

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

    public function apply(): MigrateVisitEntryData
    {
        $this->moduleDataSetup->startSetup();
        $this->tableDataMigrator->migrateData(
            $this->moduleDataSetup,
            self::OLD_TABLE_NAME,
            VisitHistoryEntryResource::TABLE_NAME,
            [
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
