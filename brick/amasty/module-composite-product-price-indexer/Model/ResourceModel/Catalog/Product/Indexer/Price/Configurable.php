<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Composite Product Price Indexer
 */

namespace Amasty\CompositeProductPriceIndexer\Model\ResourceModel\Catalog\Product\Indexer\Price;

use Amasty\CompositeProductPriceIndexer\Model\Indexer\Price\FullReindexFlag;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;

class Configurable
{
    public const MAIN_INDEX_TABLE = 'catalog_product_index_price';
    public const TABLE_SUFFIX = '_temp';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var string
     */
    private $productIdLink;

    /**
     * @var FullReindexFlag
     */
    private $fullReindexFlag;

    public function __construct(
        ResourceConnection $resourceConnection,
        TableResolver $tableResolver,
        ProductResource $productResource,
        ?FullReindexFlag $fullReindexFlag = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableResolver = $tableResolver;
        $this->productIdLink = $productResource->getLinkField();
        $this->fullReindexFlag = $fullReindexFlag ?? ObjectManager::getInstance()->get(FullReindexFlag::class);
    }

    public function addSpecialPrice(array $dimensions, array $entityIds): void
    {

        $tableName = $this->getDataTable($dimensions);
        $indexTableName = $this->getIdxTable($dimensions);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(['main_table' => $tableName]);

        $select->joinInner(
            ['simple_link' => $this->resourceConnection->getTableName('catalog_product_super_link')],
            'simple_link.product_id=main_table.entity_id',
            []
        );
        if ($this->productIdLink == 'row_id') {
            $select->joinInner(
                ['product_link' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'simple_link.parent_id=product_link.row_id',
                ['parent_id' => 'product_link.entity_id']
            );
            $select->where('product_link.entity_id IN (?)', $entityIds);
        } else {
            $select->columns(['parent_id' => 'simple_link.parent_id']);
            $select->where('simple_link.parent_id IN (?)', $entityIds);
        }

        $select->where('main_table.price > main_table.final_price and main_table.final_price > 0');

        $select->group(['simple_link.parent_id', 'main_table.customer_group_id', 'main_table.website_id']);

        $insertData = $connection->fetchAll($select);

        if (!empty($insertData)) {
            foreach ($insertData as &$row) {
                if (isset($row['parent_id'])) {
                    $row['entity_id'] = $row['parent_id'];
                    unset($row['parent_id']);
                }
            }

            $connection->insertOnDuplicate(
                $indexTableName,
                $insertData,
                ['price', 'final_price']
            );
        }
    }

    private function getDataTable(array $dimensions): string
    {
        $tableName = $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions);
        if ($this->fullReindexFlag->isFullReindex()) {
            $tableName .= '_replica';
        }

        return $tableName;
    }

    private function getIdxTable(array $dimensions): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions) . self::TABLE_SUFFIX;
    }
}
