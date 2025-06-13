<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

namespace Amasty\CompositeProductPriceIndexer\Model\ResourceModel\Catalog\Product\Indexer\Price;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Framework\Stdlib\DateTime;

class Simple
{
    public const MAIN_INDEX_TABLE = 'catalog_product_index_price';
    public const TABLE_SUFFIX = '_temp';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var DateTime
     */
    private $date;

    public function __construct(
        DateTime $date,
        TableResolver $tableResolver,
        ResourceConnection $resource
    ) {
        $this->date = $date;
        $this->tableResolver = $tableResolver;
        $this->resource = $resource;
    }

    public function addRulePrice(array $dimensions, array $entityIds)
    {
        $columns = [
            'entity_id' => 'main_table.entity_id',
            'customer_group_id' => 'main_table.customer_group_id',
            'website_id' => 'main_table.website_id',
            'tax_class_id' => 'main_table.tax_class_id',
            'price' => 'main_table.price',
            'final_price' => new \Zend_Db_Expr('LEAST(main_table.final_price, rule_index.rule_price)'),
            'min_price' => new \Zend_Db_Expr('LEAST(main_table.min_price, rule_index.rule_price)'),
            'max_price' => new \Zend_Db_Expr('LEAST(main_table.max_price, rule_index.rule_price)'),
            'tier_price' => 'main_table.tier_price',
        ];
        $indexTableName = $this->getIdxTable($dimensions);

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $indexTableName],
            $columns
        );
        $conditions = [
            'rule_index.product_id = main_table.entity_id',
            'rule_index.website_id = main_table.website_id',
            'rule_index.customer_group_id = main_table.customer_group_id'

        ];
        $select->joinInner(
            ['rule_index' => $this->resource->getTableName('catalogrule_product_price')],
            implode(' AND ', $conditions),
            []
        );
        $now = new \DateTime();
        $select->where('rule_index.rule_date = ?', $this->date->formatDate($now, false));
        if ($entityIds) {
            $select->where('main_table.entity_id IN (?)', $entityIds);
        }

        $insertData = $connection->fetchAll($select);
        if (!empty($insertData)) {
            $connection->insertOnDuplicate(
                $indexTableName,
                $insertData,
                ['final_price', 'min_price', 'max_price']
            );
        }
    }

    private function getIdxTable(array $dimensions): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions) . self::TABLE_SUFFIX;
    }
}
