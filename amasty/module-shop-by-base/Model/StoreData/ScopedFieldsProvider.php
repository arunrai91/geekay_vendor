<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\StoreData;

use Magento\Framework\App\ResourceConnection;

class ScopedFieldsProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $tableCache = [];

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function getNullableFields(string $tableName): array
    {
        if (isset($this->tableCache[$tableName]['nullable'])) {
            return $this->tableCache[$tableName]['nullable'];
        }

        return $this->loadFieldData($tableName)['nullable'] ?? [];
    }

    public function getNotNullableFields(string $tableName): array
    {
        if (isset($this->tableCache[$tableName]['not_nullable'])) {
            return $this->tableCache[$tableName]['not_nullable'];
        }

        return $this->loadFieldData($tableName)['not_nullable'] ?? [];
    }

    private function loadFieldData(string $tableName): array
    {
        $result = [];
        $connection = $this->resourceConnection->getConnection();

        foreach ($connection->describeTable($tableName) as $columnName => $columnConfig) {
            if ($columnConfig['NULLABLE'] ?? false) {
                $result['nullable'][] = $columnName;
            } else {
                $result['not_nullable'][] = $columnName;
            }
        }

        return $this->tableCache[$tableName] = $result;
    }
}
