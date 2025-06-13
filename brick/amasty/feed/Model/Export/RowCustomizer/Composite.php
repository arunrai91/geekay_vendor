<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as ExportProduct;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite as CompositeBase;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Composite extends CompositeBase
{
    public const CUSTOM_DATA_KEY = 'amasty_custom_data';

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var bool
     */
    private $isParentExport = false;

    /**
     * @var RowCustomizerInterface[]
     */
    private $customizerObjects = [];

    /**
     * @var ExportProduct
     */
    private $exportModel;

    public function initFromProfile(ExportProduct $exportProduct): void
    {
        $this->exportModel = $exportProduct;
        $attributeStorage = $exportProduct->getAttributesStorage();

        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_IMAGE_ATTRIBUTE)) {
            unset($this->customizers['imagesData']);
        }
        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_GALLERY_ATTRIBUTE)) {
            unset($this->customizers['galleryData']);
        }
        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_CATEGORY_ATTRIBUTE)
            && !$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_CATEGORY_PATH_ATTRIBUTE)
            && !$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_ATTRIBUTE)
            && !$attributeStorage->getAttributesByType(
                FeedAttributesStorage::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE
            )
        ) {
            unset($this->customizers['categoryData']);
        }
        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_CUSTOM_FIELD_ATTRIBUTE)) {
            unset($this->customizers['customFieldData']);
        }
        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_ADVANCED_ATTRIBUTE)) {
            unset($this->customizers['advancedData']);
        }
        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_URL_ATTRIBUTE)) {
            unset($this->customizers['urlData']);
        }
        if (!$attributeStorage->getAttributesByType(FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE)) {
            unset($this->customizers['priceData']);
        }
        if ($this->isParentExport || !$attributeStorage->hasParentAttributes()) {
            unset($this->customizers['relationData']);
        }
        if (!$this->isParentExport
            || !isset(
                $attributeStorage->getAttributesByType(
                    FeedAttributesStorage::PREFIX_URL_ATTRIBUTE
                )['configurable']
            )
        ) {
            unset($this->customizers['configurableProduct']);
        }
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    public function setIsParentExport(bool $isParentExport): void
    {
        $this->isParentExport = $isParentExport;
    }

    public function prepareData($collection, $productIds)
    {
        foreach ($this->customizers as $key => $className) {
            $collection->setStoreId($this->storeId);
            $this->getCustomizerObject($className)->prepareData($collection, $productIds);
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $dataRow['product_id'] = $productId;
        if (!isset($dataRow[self::CUSTOM_DATA_KEY])) {
            $dataRow[self::CUSTOM_DATA_KEY] = [];
        }

        foreach ($this->customizers as $className) {
            $dataRow = $this->getCustomizerObject($className)->addData($dataRow, $productId);
        }

        return $dataRow;
    }

    public function addHeaderColumns($columns)
    {
        foreach ($this->customizers as $className) {
            $columns = $this->getCustomizerObject($className)->addHeaderColumns($columns);
        }
        return $columns;
    }

    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        foreach ($this->customizers as $className) {
            $additionalRowsCount = $this->getCustomizerObject(
                $className
            )->getAdditionalRowsCount(
                $additionalRowsCount,
                $productId
            );
        }
        return $additionalRowsCount;
    }

    private function getCustomizerObject(string $className): RowCustomizerInterface
    {
        if (!isset($this->customizerObjects[$className])) {
            $this->customizerObjects[$className] = $this->objectManager->create(
                $className,
                ['export' => $this->exportModel]
            );
        }

        return $this->customizerObjects[$className];
    }
}
