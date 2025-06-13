<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Setup\Operation;

use Magento\Framework\Setup\ModuleDataSetupInterface;

class TableDataMigrator
{
    public function migrateData(
        ModuleDataSetupInterface $setup,
        string $tableFrom,
        string $tableTo,
        array $mapFields = []
    ): void {
        if ($setup->tableExists($tableFrom) && $setup->tableExists($tableTo)) {
            $connection = $setup->getConnection();
            $tableFrom = $setup->getTable($tableFrom);
            $tableTo = $setup->getTable($tableTo);
            $fields = [];
            foreach ($connection->describeTable($tableFrom) as $columnName => $columnConfig) {
                $columnName = strtolower($columnName);
                $alias = $mapFields[$columnName] ?? $columnName;
                $fields[$alias] = $columnName;
            }

            $sourceFields = array_keys($fields);
            $targetFields = array_map('strtolower', array_keys($connection->describeTable($tableTo)));
            if (array_diff($sourceFields, $targetFields)) {
                throw new \RuntimeException('Invalid data source to migrate.');
            }

            $select = $connection->select()->from($tableFrom, $fields);
            $connection->query(
                $connection->insertFromSelect($select, $tableTo, $sourceFields)
            );
        }
    }
}
