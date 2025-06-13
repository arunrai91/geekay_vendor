<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config as FeedConfigProvider;
use Amasty\Feed\Model\Export\Product\Attributes\AttributesCache;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesProcessor;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorageFactory;
use Amasty\Feed\Model\Export\Product\Attributes\ProductFeedAttributesPool;
use Amasty\Feed\Model\Export\Product\GetMediaGalleryData;
use Amasty\Feed\Model\Export\RowCustomizer\Composite;
use Amasty\Feed\Model\Export\RowCustomizer\CompositeFactory;
use Amasty\Feed\Model\Export\RowCustomizer\Relation;
use Amasty\Feed\Model\InventoryResolver;
use Amasty\Feed\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Category\StoreCategories;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttrCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as ProductOptionCollectionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogImportExport\Model\Export\Product as ProductBase;
use Magento\CatalogImportExport\Model\Export\Product\Type\Factory as TypeFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttrSetCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Product extends ProductBase
{
    private const IS_IN_STOCK_ATTRIBUTE_CODE = 'is_in_stock';
    public const GALLERY_IMAGE_QTY = 5;
    public const ROW_CATEGORIES_KEY = 'rowCategories';

    /**
     * @var array
     */
    protected $_fieldsMap = [
        ProductBase::COL_TYPE => 'product_type',
        ProductBase::COL_PRODUCT_WEBSITES => 'product_websites'
    ];

    /**
     * @var array
     */
    protected $_bannedAttributes = [
        'media_gallery',
        'gallery',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'pattern'
    ];

    /**
     * @var Composite
     */
    protected $rowCustomizer;

    /**
     * @var int|string
     */
    private $storeId;

    /**
     * @var Composite
     */
    private $rowCustomizerComposite;

    /**
     * @var array
     */
    private $categoriesPath = [];

    /**
     * @var array
     */
    private $categoriesLast = [];

    /**
     * @var array
     */
    private $multirawData;

    /**
     * @var int[]
     */
    private $matchingProductIds;

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var CollectionFactory
     */
    private $collectionAmastyFactory;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    /**
     * @var StoreCategories
     */
    private $storeCategories;

    /**
     * @var ProductFeedAttributesPool|null
     */
    private $feedAttributesPool;

    /**
     * @var FeedInterface|null
     */
    private $feedProfile;

    /**
     * @var GetMediaGalleryData
     */
    private $getMediaGalleryData;

    /**
     * @var FeedAttributesProcessor
     */
    private $feedAttributesProcessor;

    /**
     * @var FeedAttributesStorageFactory
     */
    private $attributesStorageFactory;

    /**
     * @var FeedAttributesStorage
     */
    private $attributesStorage;

    /**
     * @var FeedConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $userDefinedAttributes = [];

    /**
     * @var AttributesCache
     */
    private $attributesCache;

    /**
     * @var Emulation
     */
    private $emulation;

    public function __construct(
        StockRegistry $stockRegistry,
        TimezoneInterface $localeDate,
        Config $config,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        StoreCategories $storeCategories,
        LoggerInterface $logger,
        ProductCollectionFactory $collectionFactory,
        ConfigInterface $exportConfig,
        ProductFactory $productFactory,
        AttrSetCollectionFactory $attrSetColFactory,
        CategoryCollectionFactory $categoryColFactory,
        ItemFactory $itemFactory,
        ProductOptionCollectionFactory $optionColFactory,
        ProductAttrCollectionFactory $attributeColFactory,
        TypeFactory $typeFactory,
        LinkTypeProvider $linkTypeProvider,
        CompositeFactory $rowCustomizerFactory,
        FeedConfigProvider $configProvider,
        CollectionFactory $collectionAmastyFactory,
        InventoryResolver $inventoryResolver,
        ProductFeedAttributesPool $feedAttributesPool,
        GetMediaGalleryData $getMediaGalleryData,
        FeedAttributesProcessor $feedAttributesProcessor,
        FeedAttributesStorageFactory $attributesStorageFactory,
        AttributesCache $attributesCache,
        Emulation $emulation,
        FeedInterface $feedProfile = null,
        int $storeId = null
    ) {
        $this->rowCustomizerComposite = $rowCustomizerFactory->create();
        $this->collectionAmastyFactory = $collectionAmastyFactory;
        $this->stockRegistry = $stockRegistry;
        $this->inventoryResolver = $inventoryResolver;
        $this->storeCategories = $storeCategories;
        $this->feedAttributesPool = $feedAttributesPool;
        $this->getMediaGalleryData = $getMediaGalleryData;
        $this->feedAttributesProcessor = $feedAttributesProcessor;
        $this->attributesStorageFactory = $attributesStorageFactory;
        $this->configProvider = $configProvider;
        $this->attributesCache = $attributesCache;
        $this->storeId = $storeId;
        $this->feedProfile = $feedProfile;
        $this->emulation = $emulation;

        parent::__construct(
            $localeDate,
            $config,
            $resource,
            $storeManager,
            $logger,
            $collectionFactory,
            $exportConfig,
            $productFactory,
            $attrSetColFactory,
            $categoryColFactory,
            $itemFactory,
            $optionColFactory,
            $attributeColFactory,
            $typeFactory,
            $linkTypeProvider,
            $this->rowCustomizerComposite
        );
    }

    public function export(bool $lastPage = false): string
    {
        $exportData = $this->getRawExport();

        $writer = $this->getWriter();
        if ($this->page === 0) {
            $writer->writeHeader();
        }

        foreach ($exportData as $dataRow) {
            $writer->writeDataRow($dataRow);
        }

        if ($lastPage) {
            $writer->writeFooter();
        }

        return $writer->getContents();
    }

    /**
     * Returns data prepared to file write
     * @return array
     */
    public function getRawExport(): array
    {
        $this->_initStores();
        $this->rowCustomizerComposite->setStoreId((int)$this->storeId);

        $entityCollection = $this->_getEntityCollection(true);
        $entityCollection->setStoreId($this->storeId);
        $this->_prepareEntityCollection($entityCollection);
        $exportData = $this->getExportData();

        $result = [];
        foreach ($exportData as $dataRow) {
            $result[$dataRow['sku']] = $this->prepareRowBeforeWrite($dataRow);
        }

        return $result;
    }

    /**
     * Exports parent data for products
     * @see Relation::getParentExportData
     *
     * @param int[] $ids
     * @return array
     */
    public function exportParents(array $ids): array
    {
        $this->_initStores();

        $entityCollection = $this->_getEntityCollection(true);
        $entityCollection->setStoreId($this->storeId);
        $this->_prepareEntityCollection($entityCollection);

        $entityCollection->addStoreFilter($this->storeId);
        $entityCollection->addAttributeToFilter(
            $this->getProductEntityLinkField(),
            ['in' => $ids]
        );

        if ($this->getFeedProfile()->getExcludeDisabled()) {
            $entityCollection->addAttributeToFilter(
                'status',
                ['eq' => Status::STATUS_ENABLED]
            );
        }
        $parentsData = $this->getExportData();

        $this->rowCustomizerComposite->setIsParentExport(true);
        $this->rowCustomizerComposite->setStoreId((int)$this->storeId);
        $this->rowCustomizerComposite->setIsParentExport(false);

        return $parentsData;
    }

    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;

        return $this;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param int[] $matchingProductIds entity_ids
     * @return $this
     */
    public function setMatchingProductIds(array $matchingProductIds): self
    {
        $this->matchingProductIds = $matchingProductIds;

        return $this;
    }

    public function getFeedProfile(): FeedInterface
    {
        return $this->feedProfile;
    }

    public function getAttributesStorage(): FeedAttributesStorage
    {
        if ($this->attributesStorage === null) {
            $this->attributesStorage = $this->attributesStorageFactory->create();
        }

        return $this->attributesStorage;
    }

    public function setAttributes(array $attributes): self
    {
        $this->getAttributesStorage()->setAttributes($attributes);

        return $this;
    }

    public function setParentAttributes(array $attributes): self
    {
        $this->getAttributesStorage()->setParentAttributes($attributes);

        return $this;
    }

    public function getMultirawData(): array
    {
        return $this->multirawData;
    }

    public function getCategoriesPath(): array
    {
        return $this->categoriesPath;
    }

    public function getCategoriesLast(): array
    {
        return $this->categoriesLast;
    }

    /**
     * Uses as OptionSource
     * @return string[]
     */
    public function getExportAttrCodesList(): array
    {
        $list = [];
        $exportAttrCodes = $this->_getExportAttrCodes();
        foreach ($this->filterAttributeCollection($this->getAttributeCollection())->getItems() as $attribute) {
            $attrCode = $attribute->getAttributeCode();
            if (in_array($attrCode, $exportAttrCodes, true)) {
                $list[$attrCode] = $attribute->getFrontendLabel();
            }
        }

        return $list;
    }

    public function filterAttributeCollection(Collection $collection): Collection
    {
        $basicAttributes = $this->getAttributesStorage()->getAttributesByType(
            FeedAttributesStorage::PREFIX_BASIC_ATTRIBUTE
        );
        $productAttributes = $this->getAttributesStorage()->getAttributesByType(
            FeedAttributesStorage::PREFIX_PRODUCT_ATTRIBUTE
        );
        $imageAttributes = $this->getAttributesStorage()->getAttributesByType(
            FeedAttributesStorage::PREFIX_IMAGE_ATTRIBUTE
        );
        $attributes = array_merge($basicAttributes, $productAttributes, $imageAttributes);
        $attributes['url_key'] = 'url_key';

        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (!isset($attributes[$attribute->getAttributeCode()])
                && $this->getAttributesStorage()->getAttributes()
            ) {
                $collection->removeItemByKey($attribute->getId());
            }
        }

        return $collection;
    }

    /**
     * Overriding method to use actual store.
     */
    public function getAttributeOptions(AbstractAttribute $attribute): array
    {
        $options = [];
        if ($attribute->usesSource()) {
            $index = in_array($attribute->getAttributeCode(), $this->_indexValueAttributes, false) ? 'value' : 'label';
            $attribute->setStoreId($this->storeId ?: Store::DEFAULT_STORE_ID);
            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : [$option] as $innerOption) {
                        if ($innerOption['value'] !== '') { // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = (string)$innerOption[$index];
                        }
                    }
                }
                //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Exception $e) {
                // ignore exceptions connected with source models
            }
        }

        return $options;
    }

    public function getMediaGallery(array $productIds): array
    {
        return $this->getMediaGalleryData->execute($productIds, (int)$this->storeId);
    }

    protected function getExportData(): array
    {
        $exportData = [];

        $rawData = $this->collectRawData();
        $multiRowData = $this->collectMultirawData();
        $productIds = array_keys($rawData);
        $stockItemRows = $this->inventoryResolver->getInventoryData($productIds);

        $this->rowCustomizer->initFromProfile($this);
        $this->rowCustomizer->prepareData($this->_getEntityCollection(), $productIds);
        foreach ($rawData as $productId => $productData) {
            foreach ($productData as $dataRow) {
                if (isset($stockItemRows[$productId])) {
                    $dataRow += $stockItemRows[$productId];
                }
                $exportData[] = $this->addMultirawCustomizerData($dataRow, $multiRowData);
            }
        }

        return $exportData;
    }

    /**
     * Overriding original method to reproduce missed $this->userDefinedAttributes processing logic part
     * Original method is using private property so we can't restore it from cache in initAttributes() method
     * @see self::initAttributes()
     */
    protected function collectRawData(): array
    {
        $rawData = parent::collectRawData();

        $datetimeUserAttributes = array_intersect(
            array_keys($this->_attributeTypes, 'datetime'),
            $this->userDefinedAttributes
        );
        foreach ($rawData as &$productData) {
            foreach ($productData as &$dataRow) {
                foreach (array_intersect(array_keys($dataRow), $datetimeUserAttributes) as $datetimeAttribute) {
                    $dataRow[$datetimeAttribute] = $this->_localeDate->formatDateTime(
                        new \DateTime($dataRow[$datetimeAttribute]),
                        \IntlDateFormatter::SHORT,
                        \IntlDateFormatter::NONE,
                        null,
                        date_default_timezone_get()
                    );
                }
            }
        }

        return $rawData;
    }

    /**
     * Overriding method to retrieve attributes data from cache
     */
    protected function initAttributes(): self
    {
        try {
            // emulating store view for correct translations of attribute labels
            $this->emulation->startEnvironmentEmulation((int)$this->storeId, Area::AREA_FRONTEND, true);
            $this->initCachedAttributes();
        } catch (\Exception $e) {
            if (empty($this->_attributeValues)) { // exception on emulation
                $this->initCachedAttributes();
            }
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }

        return $this;
    }

    protected function collectMultirawData(): array
    {
        if (!$this->multirawData) {
            $data = [];
            $rowCategories = [];

            $collection = $this->_getEntityCollection();
            $collection->setStoreId($this->storeId);
            $collection->addCategoryIds()->addWebsiteNamesToResult();
            foreach ($collection as $item) {
                $rowCategories[$item->getId()] = $item->getCategoryIds();
            }
            $collection->clear();

            $allCategoriesIds = array_merge(array_keys($this->_categories), array_keys($this->_rootCategories));
            foreach ($rowCategories as &$categories) {
                $categories = array_intersect($categories, $allCategoriesIds);
            }
            unset($categories);

            $data[self::ROW_CATEGORIES_KEY] = $rowCategories;
            $this->multirawData = $data;
        }

        return $this->multirawData;
    }

    protected function getItemsPerPage(): int
    {
        return $this->configProvider->getItemsPerPage();
    }

    protected function _getEntityCollection($resetCollection = false)
    {
        if ($resetCollection || empty($this->_entityCollection)) {
            $this->_entityCollection = $this->collectionAmastyFactory->create();
        }

        return $this->_entityCollection;
    }

    protected function _prepareEntityCollection(AbstractCollection $collection): AbstractCollection
    {
        $result = parent::_prepareEntityCollection($collection);
        if ($this->matchingProductIds) {
            $result->addFieldToFilter('entity_id', ['in' => $this->matchingProductIds]);
        }

        return $result;
    }

    protected function _initStores(): self
    {
        $this->storeId = $this->_storeManager->isSingleStoreMode()
            ? Store::DEFAULT_STORE_ID
            : $this->storeId;
        $this->_storeIdToCode = [
            $this->storeId => $this->_storeManager->getStore($this->storeId)->getCode()
        ];
        $this->_storeManager->setCurrentStore($this->storeId);

        return $this;
    }

    protected function initCategories(): self
    {
        $collection = $this->_categoryColFactory->create()->addNameToResult();
        foreach ($collection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize = count($structure);

            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    if ($collection->getItemById($structure[$i])) {
                        $path[$structure[$i]] = $collection->getItemById($structure[$i])->getName();
                    } else {
                        $path[$structure[$i]] = null;
                    }
                }
                $this->categoriesPath[$category->getId()] = $path;
                $this->_rootCategories[$category->getId()] = array_shift($path);
                if ($pathSize > 2) {
                    $this->_categories[$category->getId()] = implode('/', $path);
                }
                $this->categoriesLast[$category->getId()] = end($this->categoriesPath[$category->getId()]);
            }
        }

        return $this;
    }

    protected function updateDataWithCategoryColumns(&$dataRow, &$rowCategories, $productId): bool
    {
        if (isset($dataRow[Composite::CUSTOM_DATA_KEY])) {
            $dataRow[Composite::CUSTOM_DATA_KEY] = [];
        }

        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];
        if (!empty($rowCategories[$productId])) {
            $categories = $rowCategories[$productId];
            $storeGroupId = (int)$this->_storeManager->getStore()->getStoreGroupId();
            $categoriesInCurrentStore = array_intersect(
                $categories,
                $this->storeCategories->getCategoryIds($storeGroupId)
            );
            $currentCategoryId = current($categoriesInCurrentStore);
            $lastCategoryId = end($categoriesInCurrentStore);

            $customData[FeedAttributesStorage::PREFIX_CATEGORY_ATTRIBUTE]
            [FeedAttributesStorage::FIRST_SELECTED_CATEGORY] =
                $this->categoriesLast[$currentCategoryId] ?? '';
            $customData[FeedAttributesStorage::PREFIX_CATEGORY_ATTRIBUTE]['category'] =
                $this->categoriesLast[$lastCategoryId] ?? '';
            $customData[FeedAttributesStorage::PREFIX_CATEGORY_ID_ATTRIBUTE] = $lastCategoryId;
        }
        $result = parent::updateDataWithCategoryColumns($dataRow, $rowCategories, $productId);

        if (isset($dataRow[self::COL_CATEGORY])) {
            $customData[FeedAttributesStorage::PREFIX_CATEGORY_PATH_ATTRIBUTE]['category']
                = $dataRow[self::COL_CATEGORY];
        }

        return $result;
    }

    private function initCachedAttributes(): void
    {
        $feedId = $this->feedProfile ? $this->feedProfile->getEntityId() : null;
        if ($feedId !== null && $this->attributesCache->get(AttributesCache::KEY_VALUES . $feedId)) {
            $this->_attributeValues = $this->attributesCache->get(AttributesCache::KEY_VALUES . $feedId);
            $this->_attributeTypes = $this->attributesCache->get(AttributesCache::KEY_TYPES . $feedId);
            $this->userDefinedAttributes = $this->attributesCache->get(AttributesCache::KEY_USER_DEFINED . $feedId);
        } else {
            $this->attributesCache->flush((int)$feedId);
            foreach ($this->getAttributeCollection() as $attribute) {
                $this->_attributeValues[$attribute->getAttributeCode()] = $this->getAttributeOptions($attribute);
                $this->_attributeTypes[$attribute->getAttributeCode()] =
                    Import::getAttributeType($attribute);
                if ($attribute->getIsUserDefined()) {
                    $this->userDefinedAttributes[] = $attribute->getAttributeCode();
                }
            }
            if ($feedId) {
                $this->attributesCache->save(AttributesCache::KEY_VALUES . $feedId, $this->_attributeValues);
                $this->attributesCache->save(AttributesCache::KEY_TYPES . $feedId, $this->_attributeTypes);
                $this->attributesCache->save(AttributesCache::KEY_USER_DEFINED . $feedId, $this->userDefinedAttributes);
            }
        }
    }

    /**
     * Applying row customizers to the data row
     */
    private function addMultirawCustomizerData(array $dataRow, array &$multiRowData): array
    {
        if (array_key_exists('product_id', $dataRow)) {
            $productId = $dataRow['product_id'];
            $this->updateDataWithCategoryColumns($dataRow, $multiRowData[self::ROW_CATEGORIES_KEY], $productId);
            $dataRow = $this->rowCustomizer->addData($dataRow, $productId);
        }

        return $dataRow;
    }

    /**
     * Applying custom fields mapping and formatting row data before write
     */
    private function prepareRowBeforeWrite(array &$dataRow): array
    {
        $exportRow = [];

        $dataRow = $this->_customFieldsMapping($dataRow);
        $attributes = $this->getAttributesStorage()->getAttributes();
        $parentAttributes = $this->getAttributesStorage()->getParentAttributes();
        if (!empty($attributes)) {
            $this->createExportRow($attributes, $dataRow, [], $exportRow);
        }

        if (!empty($parentAttributes)) {
            $parentDataRow = $this->_customFieldsMapping(
                $dataRow[Composite::CUSTOM_DATA_KEY][Relation::CUSTOM_DATA_KEY] ?? []
            );
            $this->createExportRow(
                $parentAttributes,
                $parentDataRow,
                $dataRow,
                $exportRow
            );
        }

        return $exportRow;
    }

    /**
     * Applying additional attribute processing logic. Some attribute values should be received from custom data
     */
    private function createExportRow(
        array $attributes,
        array $dataRow,
        array $childDataRow,
        array &$exportRow
    ): void {
        $basicTypes = $this->feedAttributesPool->getBasicTypes();
        $customTypes = $this->feedAttributesPool->getCustomTypes();
        $postfix = count($childDataRow) > 0 ? '|parent' : '';
        foreach ($basicTypes as $type) {
            if (isset($attributes[$type]) && is_array($attributes[$type])) {
                foreach ($attributes[$type] as $code) {
                    $attributeValue = $this->feedAttributesProcessor->getAttributeValue($dataRow, $code)
                        ?: $this->feedAttributesProcessor->getAttributeValue($childDataRow, $code);

                    if ($code === self::IS_IN_STOCK_ATTRIBUTE_CODE) {
                        $attributeValue = $this->feedAttributesProcessor->getAttributeValue($dataRow, $code);
                        if ($attributeValue === null) {
                            $attributeValue = $this->stockRegistry->getStockStatusBySku(
                                $dataRow['sku'],
                                $this->_storeManager->getWebsite()->getId()
                            )->getStockStatus();
                        }
                    }

                    if ($attributeValue !== false) {
                        $exportRow[$type . '|' . $code . $postfix] = $attributeValue;
                    }
                }
            }
        }

        $customData = (array)($dataRow[Composite::CUSTOM_DATA_KEY] ?? []);
        $childCustomData = (array)($childDataRow[Composite::CUSTOM_DATA_KEY]?? []);
        foreach ($customTypes as $type) {
            if (isset($attributes[$type]) && is_array($attributes[$type])) {
                foreach ($attributes[$type] as $code) {
                    $customDataValue = $this->feedAttributesProcessor->getAttrValueFromCustomData(
                        $customData,
                        (string)$type,
                        (string)$code
                    );
                    $childCustomDataValue = $this->feedAttributesProcessor->getAttrValueFromCustomData(
                        $childCustomData,
                        (string)$type,
                        (string)$code
                    );
                    if ($customDataValue !== null && $customDataValue !== '') {
                        $exportRow[$type . '|' . $code . $postfix] = $customDataValue;
                    } elseif ($childCustomDataValue !== null) {
                        $exportRow[$type . '|' . $code . $postfix] = $childCustomDataValue;
                    }
                }
            }
        }
    }
}
