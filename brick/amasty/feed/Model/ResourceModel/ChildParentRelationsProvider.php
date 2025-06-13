<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

class ChildParentRelationsProvider
{
    public const KEY_PARENT_ID = 'parent_id';
    public const KEY_CHILD_ID = 'child_id';
    public const KEY_TYPE_ID = 'type_id';

    /**
     * @var EntityMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource
    ) {
        $this->productMetadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->resource = $resource;
    }

    /**
     * @param int[] $productIds
     * @return array [parent_id, child_id, type_id] - parent_id is linkField
     */
    public function getRelationsData(array $productIds): array
    {
        $linkField = $this->productMetadata->getLinkField();

        $connection = $this->resource->getConnection();
        $select = $connection->select();
        $select->from(
            ['r' => $this->resource->getTableName('catalog_product_relation')],
            [self::KEY_PARENT_ID => 'r.parent_id', self::KEY_CHILD_ID => 'r.child_id']
        )->joinLeft(
            ['pe' => $this->resource->getTableName('catalog_product_entity')],
            'pe.' . $linkField . ' = r.parent_id',
            [self::KEY_TYPE_ID => 'pe.type_id']
        )->where(self::KEY_CHILD_ID . ' IN (?)', $productIds);
        $relationData = (array)$connection->fetchAll($select);

        return array_filter($relationData, static function ($row) {
            return !empty($row[self::KEY_TYPE_ID]);
        });
    }
}
