<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\ResourceModel\Inventory;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

class GetGroupedSimplesReservationQty
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(ResourceConnection $resourceConnection, MetadataPool $metadataPool)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return array ['sku' => 'qty', ...]
     */
    public function execute(string $productSku, int $stockId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $connection->select()->from(
            ['reservation' => $this->resourceConnection->getTableName('inventory_reservation')],
            ['sku', 'qty' => 'SUM(quantity)']
        )->joinInner(
            ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'product_entity.sku = reservation.sku',
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
            'stock_id = ?',
            $stockId
        )->where(
            'parent_product_entity.sku = ?',
            $productSku
        )->group(
            'reservation.sku'
        );

        return $connection->fetchPairs($select);
    }
}
