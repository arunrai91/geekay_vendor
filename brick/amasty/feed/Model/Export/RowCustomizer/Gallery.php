<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Gallery implements RowCustomizerInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $urlPrefix;

    /**
     * @var array
     */
    private $gallery = [];

    /**
     * @var Product
     */
    private $export;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $entityIdRowIdMap = [];

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        ResourceConnection $resource,
        Product $export
    ) {
        $this->storeManager = $storeManager;
        $this->export = $export;
        $this->productMetadata = $productMetadata;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    public function prepareData($collection, $productIds)
    {
        $this->prepareEntityIdToRowIdMap($productIds);
        if ($this->export->getAttributesStorage()->hasAttributes(FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE)) {
            $this->urlPrefix = $this->storeManager->getStore($collection->getStoreId())
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product';
            $this->gallery = $this->export->getMediaGallery($productIds);
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $productId = $this->entityIdRowIdMap[$productId] ?? $productId;
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];
        $gallery = $this->getGallery();
        $gallery = $gallery[$productId] ?? [];
        $galleryImg = [];

        foreach ($gallery as $data) {
            $data['_media_image'] = '/' . ltrim($data['_media_image'] ?? '', '/');
            if (!isset($customData['image'])
                || !in_array($this->urlPrefix . $data['_media_image'], $customData['image'], true)
            ) {
                $galleryImg[] = $this->urlPrefix . $data['_media_image'];
            }
        }
        $customData[FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE] = $this->getGalleryOptions($galleryImg);

        return $dataRow;
    }

    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    protected function getGalleryOptions(array $galleryImg): array
    {
        return [
            'image_1' => $galleryImg[0] ?? null,
            'image_2' => $galleryImg[1] ?? null,
            'image_3' => $galleryImg[2] ?? null,
            'image_4' => $galleryImg[3] ?? null,
            'image_5' => $galleryImg[4] ?? null,
        ];
    }

    private function getGallery(): array
    {
        return $this->gallery;
    }

    private function prepareEntityIdToRowIdMap(array $productIds): void
    {
        if ($this->productMetadata->getEdition() === 'Community') {
            return;
        }
        $tableName = $this->resource->getTableName('catalog_product_entity');
        $select = $this->connection->select()
            ->from($tableName, ['entity_id', 'row_id'])
            ->where('entity_id IN (?)', $productIds)
            ->group('entity_id');
        $this->entityIdRowIdMap = $this->connection->fetchPairs($select);
    }
}
