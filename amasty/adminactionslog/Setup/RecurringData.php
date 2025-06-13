<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup;

use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogDetail as LogDetailResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryDetail as VisitHistoryDetailResource;
use Amasty\AdminActionsLog\Setup\Patch\Data\MigrateActiveSessionsData;
use Amasty\AdminActionsLog\Setup\Patch\Data\MigrateLogEntryData;
use Amasty\AdminActionsLog\Setup\Patch\Data\MigrateVisitEntryData;
use Magento\Framework\FlagManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchHistory;

class RecurringData implements InstallDataInterface
{
    private const FLAG_CODE = 'amasty_audit_upg_to_210';

    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(
        PatchHistory $patchHistory,
        FlagManager $flagManager
    ) {
        $this->patchHistory = $patchHistory;
        $this->flagManager = $flagManager;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (!$this->flagManager->getFlagData(self::FLAG_CODE)) {
            if ($this->removeTables($setup)) {
                $this->flagManager->saveFlag(self::FLAG_CODE, true);
            }
        }

        $setup->endSetup();
    }

    private function removeTables(ModuleDataSetupInterface $setup): bool
    {
        $tablesToDrop = [
            MigrateActiveSessionsData::OLD_TABLE_NAME => [
                'patchName' => MigrateActiveSessionsData::class,
                'refTable' => ''
            ],
            MigrateLogEntryData::OLD_TABLE_NAME => [
                'patchName' => MigrateLogEntryData::class,
                'refTable' => LogDetailResource::TABLE_NAME
            ],
            MigrateVisitEntryData::OLD_TABLE_NAME => [
                'patchName' => MigrateVisitEntryData::class,
                'refTable' => VisitHistoryDetailResource::TABLE_NAME
            ]
        ];
        $connection = $setup->getConnection();
        foreach ($tablesToDrop as $table => &$info) {
            if ($this->patchHistory->isApplied($info['patchName'])) {
                $tableName = $setup->getTable($table);
                if ($setup->tableExists($table)) {
                    $connection->dropTable($tableName);
                    $this->dropForeignKeys($setup, $info['refTable'], $tableName);
                }

                unset($tablesToDrop[$table]);
            }
        }

        return empty($tablesToDrop);
    }

    private function dropForeignKeys(ModuleDataSetupInterface $setup, string $tableName, string $refTable)
    {
        if ($tableName && $setup->tableExists($tableName)) {
            $connection = $setup->getConnection();
            foreach ($connection->getForeignKeys($setup->getTable($tableName)) as $keyInfo) {
                if ($keyInfo['REF_TABLE_NAME'] === $refTable) {
                    $connection->dropForeignKey($keyInfo['TABLE_NAME'], $keyInfo['FK_NAME']);
                }
            }
        }
    }
}
