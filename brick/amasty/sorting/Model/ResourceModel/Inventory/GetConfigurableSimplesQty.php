<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\ResourceModel\Inventory;

use Amasty\Sorting\Model\ResourceModel\Inventory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Return array of simples qty for configurable.
 * Returning qty consider min_qty.
 */
class GetConfigurableSimplesQty
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Inventory
     */
    private $inventory;

    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        Inventory $inventory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->inventory = $inventory;
    }

    /**
     * @return array ['sku' => 'qty', ...]
     */
    public function execute(string $productSku, string $websiteCode, float $globalMinQty = 0): array
    {
        $connection = $this->resourceConnection->getConnection();

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $qtyExpression = $connection->getGreatestSql([$connection->getCheckSql(
            'stock_item.use_config_min_qty = 1',
            sprintf('source_item.quantity - %f', $globalMinQty),
            'source_item.quantity - stock_item.min_qty'
        ), 0]);

        $select = $connection->select()->from(
            ['source_item' => $this->resourceConnection->getTableName('inventory_source_item')],
            ['sku', 'qty' => sprintf('SUM(%s)', $qtyExpression)]
        )->joinInner(
            ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'product_entity.sku = source_item.sku',
            []
        )->joinInner(
            ['stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'stock_item.product_id = product_entity.entity_id',
            []
        )->joinInner(
            ['parent_link' => $this->resourceConnection->getTableName('catalog_product_super_link')],
            'parent_link.product_id = product_entity.entity_id',
            []
        )->joinInner(
            ['parent_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'parent_product_entity.' . $linkField . ' = parent_link.parent_id',
            []
        )->where(
            'source_code IN (?)',
            $this->inventory->getSourceCodes($websiteCode)
        )->where(
            'parent_product_entity.sku = ?',
            $productSku
        )->where(
            'source_item.status = ?',
            1
        )->group(
            'source_item.sku'
        )->having('qty > 0');

        return $connection->fetchPairs($select);
    }
}
