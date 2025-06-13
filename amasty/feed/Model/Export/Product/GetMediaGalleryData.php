<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

class GetMediaGalleryData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EntityMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $metadataPool->getMetadata(ProductInterface::class);
    }

    /**
     * @param int[] $productIds
     * @param int $storeId
     * @return array
     */
    public function execute(array $productIds, int $storeId): array
    {
        if (empty($productIds)) {
            return [];
        }
        $connection = $this->resourceConnection->getConnection();
        $productEntityJoinField = $this->productMetadata->getLinkField();

        $select = $connection->select()->from(
            [
                'mgvte' => $this->resourceConnection->getTableName(
                    'catalog_product_entity_media_gallery_value_to_entity'
                )
            ],
            [
                "mgvte.$productEntityJoinField",
                'mgvte.value_id'
            ]
        )->joinLeft(
            ['mg' => $this->resourceConnection->getTableName('catalog_product_entity_media_gallery')],
            '(mg.value_id = mgvte.value_id)',
            [
                'mg.attribute_id',
                'filename' => 'mg.value',
            ]
        )->joinLeft(
            ['mgv' => $this->resourceConnection->getTableName('catalog_product_entity_media_gallery_value')],
            "(mg.value_id = mgv.value_id)"
            . " and (mgvte.$productEntityJoinField = mgv.$productEntityJoinField)"
            . ' and mgv.disabled = 0',
            [
                'mgv.label',
                'mgv.position',
                'mgv.disabled',
                'mgv.store_id',
            ]
        )->joinLeft(
            ['ent' => $this->resourceConnection->getTableName('catalog_product_entity')],
            "(mgvte.$productEntityJoinField = ent.$productEntityJoinField)",
            [
                'ent.entity_id'
            ]
        )->where(
            'ent.entity_id IN (?)',
            $productIds
        )->where(
            'mgv.store_id IN (?)',
            [Store::DEFAULT_STORE_ID, $storeId]
        )->order(
            'mgv.position ASC'
        )->group('mgvte.value_id');

        $rowMediaGallery = [];
        $stmt = $connection->query($select);
        while ($mediaRow = $stmt->fetch()) {
            $rowMediaGallery[$mediaRow[$productEntityJoinField]][] = [
                '_media_attribute_id' => $mediaRow['attribute_id'],
                '_media_image' => $mediaRow['filename'],
                '_media_label' => $mediaRow['label'],
                '_media_position' => $mediaRow['position'],
                '_media_is_disabled' => $mediaRow['disabled'],
                '_media_store_id' => $mediaRow['store_id'],
            ];
        }

        return $rowMediaGallery;
    }
}
