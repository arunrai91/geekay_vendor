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
use Amasty\Feed\Model\Export\ProductFactory;
use Amasty\Feed\Model\ResourceModel\ChildParentRelationsProvider;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Relation implements RowCustomizerInterface
{
    public const CUSTOM_DATA_KEY = 'parent_data';

    /**
     * @var array
     */
    private $child2parent;

    /**
     * @var array
     */
    private $parentData = [];

    /**
     * @var Product
     */
    private $export;

    /**
     * @var ProductFactory
     */
    private $productExportFactory;

    /**
     * @var ChildParentRelationsProvider
     */
    private $childParentRelationsProvider;

    public function __construct(
        Product $export,
        ProductFactory $productExportFactory,
        ChildParentRelationsProvider $childParentRelationsProvider
    ) {
        $this->export = $export;
        $this->productExportFactory = $productExportFactory;
        $this->childParentRelationsProvider = $childParentRelationsProvider;
    }

    public function prepareData($collection, $productIds)
    {
        $relationData = $this->childParentRelationsProvider->getRelationsData((array)$productIds);
        foreach ($relationData as $row) {
            $parentId = $row[ChildParentRelationsProvider::KEY_PARENT_ID] ?? null;
            if (!($childId = $row[ChildParentRelationsProvider::KEY_CHILD_ID] ?? null)) {
                continue;
            }
            $feedProfile = $this->export->getFeedProfile();
            $this->child2parent[$childId] = $this->child2parent[$childId] ?? [];
            if ($row[ChildParentRelationsProvider::KEY_TYPE_ID] === $feedProfile->getParentPriority()) {
                // Add priority element to the top of the array.
                array_unshift($this->child2parent[$childId], $parentId);
            } else {
                $this->child2parent[$childId][] = $parentId;
            }
        }

        $exportData = $this->getParentExportData(
            array_filter(array_unique(array_column($relationData, ChildParentRelationsProvider::KEY_PARENT_ID))),
            (int)$collection->getStoreId()
        );
        foreach ($exportData as $item) {
            if (array_key_exists('product_link_id', $item)) {
                $this->parentData[(int)$item['product_link_id']] = $item;
            }
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];
        foreach (($this->child2parent[$productId] ?? []) as $parentId) {
            if (isset($this->parentData[$parentId])) {
                $customData[self::CUSTOM_DATA_KEY] = $this->parentData[$parentId];
                break;
            }
        }

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

    /**
     * @param int[] $ids
     * @param int $storeId
     * @return array
     */
    private function getParentExportData(array $ids, int $storeId): array
    {
        $parentAttributes = $this->getParentAttributes();
        $parentsExport = $this->productExportFactory->create(
            ['storeId' => $storeId, 'feedProfile' => $this->export->getFeedProfile()]
        );

        return $parentsExport
            ->setAttributes($parentAttributes)
            ->setStoreId($storeId)
            ->exportParents(array_values($ids));
    }

    private function getParentAttributes(): array
    {
        $parentAttributes = [
            'product' => ['product_id' => 'product_id'],
            'url' => ['short' => 'short']
        ];
        $urlAttributes = $this->export->getAttributesStorage()->getAttributesByType(
            FeedAttributesStorage::PREFIX_URL_ATTRIBUTE
        );
        if (isset($urlAttributes['configurable'])) {
            $parentAttributes['url']['configurable'] = 'configurable';
        }

        return array_merge_recursive($this->export->getAttributesStorage()->getParentAttributes(), $parentAttributes);
    }
}
