<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as Export;
use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Amasty\Feed\Model\Export\Utils\MergedAttributeProcessor;
use Amasty\Feed\Model\Field\CustomFieldsProcessor;
use Amasty\Feed\Model\Field\CustomFieldsValidator;
use Amasty\Feed\Model\Field\CustomFieldsValidatorFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class CustomField implements RowCustomizerInterface
{
    /**
     * @var Export
     */
    private Export $export;

    /**
     * @var MergedAttributeProcessor
     */
    private MergedAttributeProcessor $mergedAttributeProcessor;

    /**
     * @var array
     */
    private array $processedFields = [];

    /**
     * @var CustomFieldsValidator|null
     */
    private ?CustomFieldsValidator $validator;

    /**
     * @var CustomFieldsProcessor|null
     */
    private ?CustomFieldsProcessor $customFieldsProcessor;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    public function __construct(
        Export $export,
        MergedAttributeProcessor $mergedAttributeProcessor,
        CustomFieldsValidator $customFieldsValidator,
        CustomFieldsProcessor $customFieldsProcessor,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->export = $export;
        $this->mergedAttributeProcessor = $mergedAttributeProcessor;
        $this->validator = $customFieldsValidator;
        $this->customFieldsProcessor = $customFieldsProcessor;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    public function prepareData($collection, $productIds)
    {
        $this->storeManager->setCurrentStore($collection->getStoreId());
        if (!$this->checkValidator($collection)) {
            return $this;
        }
        $this->mergedAttributeProcessor->prepareAttrReplacements(
            $collection,
            $this->export->getFeedProfile()
        );
        foreach ($collection->getItems() as $product) {
            $this->processProduct($product);
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $dataRow[Composite::CUSTOM_DATA_KEY][FeedAttributesStorage::PREFIX_CUSTOM_FIELD_ATTRIBUTE] = [];
        foreach ($this->processedFields[$productId] ?? [] as $code => $value) {
            $dataRow[Composite::CUSTOM_DATA_KEY][FeedAttributesStorage::PREFIX_CUSTOM_FIELD_ATTRIBUTE][$code] = $value;
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

    private function checkValidator(Collection $collection): bool
    {
        $attributesStorage = $this->export->getAttributesStorage();
        if ($attributesStorage->hasAttributes(FeedAttributesStorage::PREFIX_CUSTOM_FIELD_ATTRIBUTE)) {
            $this->validator->setCustomFields(
                $attributesStorage->getAttributesByType(FeedAttributesStorage::PREFIX_CUSTOM_FIELD_ATTRIBUTE)
            );

            return !empty($this->validator->prepareRules($collection));
        }

        return false;
    }

    private function processProduct(ProductInterface $product): void
    {
        foreach ($this->validator->getValidRules($product) as $code => $rule) {
            $this->processedFields[$product->getId()][$code] = $this->customFieldsProcessor->process($product, $rule);
        }
    }
}
