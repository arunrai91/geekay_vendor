<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Indexer\PurchaseHistory;

use Amasty\RulesPro\Model\Indexer\PurchaseHistory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

class IndexStructure implements IndexStructureInterface
{
    public const ID_FIELD = 'row_id';
    public const CUSTOMER_ID = 'customer_id';
    public const ORDERS_COUNT = 'orders_count';
    public const SUM_AMOUNT = 'sum_amount';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @var array
     */
    private $fields = [
        self::CUSTOMER_ID => [
            'type' => Table::TYPE_INTEGER,
            'size' => 10
        ],
        self::ORDERS_COUNT => [
            'type' => Table::TYPE_SMALLINT,
            'size' => 5
        ],
        self::SUM_AMOUNT => [
            'type' => Table::TYPE_DECIMAL,
            'size' => '20,6'
        ],
    ];

    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolver $indexScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    public function delete($index, array $dimensions = [])
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);
        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
    }

    public function create($index, array $fields, array $dimensions = [])
    {
        $connection = $this->resource->getConnection();
        $ddlTable = $connection->newTable($this->indexScopeResolver->resolve($index, $dimensions));
        $ddlTable->addColumn(
            self::ID_FIELD,
            Table::TYPE_BIGINT,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Index Row Id'
        );

        $fields = array_merge($this->fields, $fields);
        foreach ($fields as $fieldName => $fieldDefinition) {
            $columnOptions = [];
            if ($fieldName == self::CUSTOMER_ID) {
                $columnOptions = [
                    'unsigned' => true,
                    'nullable' => false
                ];
            }

            $ddlTable->addColumn(
                $fieldName,
                $fieldDefinition['type'] ?? Table::TYPE_TEXT,
                $fieldDefinition['size'] ?? 255,
                $columnOptions
            );
        }

        $ddlTable->addForeignKey(
            $this->resource->getFkName(
                PurchaseHistory::INDEXER_ID,
                self::CUSTOMER_ID,
                'customer_entity',
                'entity_id'
            ),
            self::CUSTOMER_ID,
            $this->resource->getTableName('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty RulesPro Purchase History Index'
        );

        $connection->createTable($ddlTable);
    }
}
