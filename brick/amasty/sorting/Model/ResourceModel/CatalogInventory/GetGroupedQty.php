<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\ResourceModel\CatalogInventory;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

class GetGroupedQty
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function execute(string $productSku, float $globalMinQty = 0): ?float
    {
        $connection = $this->resourceConnection->getConnection();

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $qtyExpression = $connection->getGreatestSql([$connection->getCheckSql(
            'stock_item.use_config_min_qty = 1',
            sprintf('stock_status.qty - %f', $globalMinQty),
            'stock_status.qty - stock_item.min_qty'
        ), 0]);

        $select = $connection->select()->from(
            ['stock_status' => $this->resourceConnection->getTableName('cataloginventory_stock_status')],
            [sprintf('SUM(%s)', $qtyExpression)]
        )->joinInner(
            ['stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'stock_item.product_id = stock_status.product_id',
            []
        )->joinInner(
            ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'product_entity.entity_id = stock_item.product_id',
            []
        )->joinInner(
            ['parent_link' => $this->resourceConnection->getTableName('catalog_product_link')],
            sprintf(
                'parent_link.linked_product_id = product_entity.entity_id AND parent_link.link_type_id = %d',
                Link::LINK_TYPE_GROUPED
            ),
            []
        )->joinInner(
            ['parent_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'parent_product_entity.' . $linkField . ' = parent_link.product_id',
            []
        )->where(
            'parent_product_entity.sku = ?',
            $productSku
        )->where(
            'stock_status.stock_status = 1'
        )->group(
            'parent_product_entity.sku'
        );
        $qty = $connection->fetchOne($select);

        return $qty ? (float)$qty : null;
    }
}
