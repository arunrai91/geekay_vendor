<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data\Product;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Amasty\ElasticSearch\Model\Indexer\Data\Product\Attribute\OptionsProvider as AttributeOptionsProvider;
use Amasty\ElasticSearch\Model\ResourceModel\ConfigurableResolver;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

class ProductDataMapper implements DataMapperInterface
{
    /**
     * @var array
     */
    private $excludedAttributes = [
        'price',
        'media_gallery',
        'tier_price',
        'quantity_and_stock_status',
        'media_gallery',
        'giftcard_amounts'
    ];

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var string[]
     */
    private $attributesExcludedFromMerge = [
        'status',
        'visibility',
        'tax_class_id'
    ];

    /**
     * @var ConfigurableResolver
     */
    private $configurableResolver;

    /**
     * @var AttributeOptionsProvider
     */
    private $attributeOptionsProvider;

    public function __construct(
        AttributeDataProvider $attributeDataProvider,
        ConfigurableResolver $configurableResolver,
        array $excludedAttributes = [],
        ?AttributeOptionsProvider $attributeOptionsProvider = null
    ) {
        $this->attributeDataProvider = $attributeDataProvider;
        $this->excludedAttributes = array_merge($this->excludedAttributes, $excludedAttributes);
        $this->configurableResolver = $configurableResolver;
        $this->attributeOptionsProvider = $attributeOptionsProvider
            ?? ObjectManager::getInstance()->get(AttributeOptionsProvider::class);
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        // reset attribute data for new store
        $documents = [];
        $skuValues = $this->configurableResolver->getRelationSkuValues(array_keys($documentData));
        foreach ($documentData as $productId => $indexData) {
            $document = ['store_id' => $storeId];
            $productIndexData = $this->getProductData($productId, $indexData, $storeId);
            $productIndexData = $this->updateSkuValues($productIndexData, $skuValues);
            foreach ($productIndexData as $attributeCode => $value) {
                if (in_array($attributeCode, $this->excludedAttributes, true)) {
                    continue;
                }

                $document[$attributeCode] = $value;
            }
            $documents[$productId] = $document;
        }

        return $documents;
    }

    /**
     * @param array $productIndexData
     * @param array $skuValues
     * @return array
     */
    private function updateSkuValues(array $productIndexData, array $skuValues)
    {
        $id = $productIndexData['entity_id'];
        if (isset($skuValues[$id]) && isset($productIndexData['sku'])) {
            $productIndexData['sku'] = array_merge([$productIndexData['sku']], explode(',', $skuValues[$id]));
            $productIndexData['sku'] = implode(' ', $productIndexData['sku']);
        }

        return $productIndexData;
    }

    /**
     * @param int $productId
     * @param array $indexData
     * @param int $storeId
     * @return array
     */
    private function getProductData($productId, array $indexData, $storeId)
    {
        $storeId = (int)$storeId;
        $productAttributes = [self::ENTITY_ID_FIELD_NAME => $productId];
        foreach ($indexData as $attributeId => $attributeValue) {
            $attribute = $this->attributeDataProvider->getAttribute($attributeId);

            if (!$attribute) {
                continue;
            }
            // phpcs:ignore
            $productAttributes = array_merge(
                $productAttributes,
                $this->prepareProductData(
                    $productId,
                    $attributeValue,
                    $attribute,
                    $storeId
                )
            );
        }

        return $productAttributes;
    }

    /**
     * @param int $productId
     * @param mixed $value
     * @param Attribute $attribute
     * @param int $storeId
     * @return array
     */
    public function prepareProductData($productId, $value, Attribute $attribute, int $storeId = Store::DEFAULT_STORE_ID)
    {
        $productAttributes = [];
        $attributeCode = $attribute->getAttributeCode();
        $attributeFrontendInput = $attribute->getFrontendInput();
        $preparedValue = $this->getProductAttributeValue($productId, $attribute, $value);
        if (is_array($value) && in_array($attributeFrontendInput, ['select', 'multiselect'], true)) {
            $productAttributes = $this->prepareOptionalAttributeValues($attribute, $value, $preparedValue, $storeId);
        } elseif ($preparedValue || $preparedValue === '0') {
            $productAttributes[$attributeCode] = $preparedValue;
            if (!is_array($preparedValue)) {
                $attributeOptions = $this->getAttributeOptions($attribute, $storeId);
                if (isset($attributeOptions[$preparedValue])) {
                    $productAttributes[$attributeCode . '_value'] = $attributeOptions[$preparedValue];
                }

                if ($attributeFrontendInput === 'date'
                    || in_array($attribute->getBackendType(), ['datetime', 'timestamp'], true)
                ) {
                    if (preg_replace('#[ 0:-]#', '', $preparedValue) !== '') {
                        $dateObj = new \DateTime($preparedValue, new \DateTimeZone('UTC'));
                        $productAttributes[$attributeCode] = $dateObj->format('c');
                    }
                }
            }
        }

        return $productAttributes;
    }

    /**
     * @param int $productId
     * @param Attribute $attribute
     * @param mixed $attributeValue
     * @return array|string|int
     */
    private function getProductAttributeValue($productId, $attribute, $attributeValue)
    {
        if (is_array($attributeValue)) {
            if (isset($attributeValue[$productId])
                && (!$attribute->getIsSearchable() || $this->isAttributeExcludedFromMerge($attribute))
            ) {
                if (in_array($attribute->getFrontendInput(), ['text', 'textarea'], true)) {
                    return $attributeValue[$productId];
                }

                $explodedValue = explode(',', (string) $attributeValue[$productId]);
                return count($explodedValue) > 1 ? $explodedValue : $attributeValue[$productId];
            }

            return $this->prepareAttributeValues($attributeValue);
        }

        return $attributeValue;
    }

    /**
     * Fix for compatibility with our Grouped Options module, due to which an attribute
     * option can have several values that come through ',' and if they are also written to the
     * elastic in this form, then the search for them will not work
     */
    private function prepareAttributeValues(array $values): array
    {
        $values = array_values(array_unique($values));

        foreach ($values as $key => $value) {
            $attrCodeValues = explode(',', (string) $value);
            unset($values[$key]);

            foreach ($attrCodeValues as $attrCodeValue) {
                $values[] = $attrCodeValue;
            }
        }

        return array_values(array_unique($values));
    }

    public function isAttributeExcludedFromMerge(Attribute $attribute)
    {
        $result = false;

        if (in_array($attribute->getAttributeCode(), $this->attributesExcludedFromMerge, true)
            || in_array($attribute->getFrontendInput(), ['text', 'textarea'], true)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param Attribute $attribute
     * @param mixed $value
     * @param mixed $preparedValue
     * @param int $storeId
     * @return array
     */
    private function prepareOptionalAttributeValues(
        Attribute $attribute,
        $value,
        $preparedValue = null,
        int $storeId = Store::DEFAULT_STORE_ID
    ) {
        /**
         * @TODO add prices mapper
         */
        $productAttributes = [];
        $attributeCode = $attribute->getAttributeCode();
        $attributeFrontendInput = $attribute->getFrontendInput();
        $attributeOptions = $this->getAttributeOptions($attribute, $storeId);
        $textAttributeValue = [];

        if ($attributeFrontendInput == 'select') {
            $productAttributes[$attributeCode] = $preparedValue;
            if ($attribute->getIsSearchable() || $attribute->getUsedForSortBy()) {
                foreach ($value as $optionIds) {
                    $optionIds = explode(',', $optionIds);
                    foreach ($optionIds as $optionId) {
                        if (isset($attributeOptions[$optionId])) {
                            $textAttributeValue[] = $attributeOptions[$optionId];
                        }
                    }
                }
            }
        } elseif ($attributeFrontendInput == 'multiselect') {
            $preparedValue = [];
            foreach ($value as $valueByAttribute) {
                $valueByAttribute = explode(',', $valueByAttribute);
                foreach ($valueByAttribute as $optionIds) {
                    $optionIds = explode(',', $optionIds);
                    foreach ($optionIds as $optionId) {
                        if (isset($attributeOptions[$optionId]) && $attribute->getIsSearchable()) {
                            $preparedValue[] = $optionId;
                            $textAttributeValue[] = $attributeOptions[$optionId];
                        } elseif (isset($attributeOptions[$optionId])) {
                            $preparedValue[] = $optionId;
                        }
                    }
                }
            }

            $productAttributes[$attributeCode] = array_values(array_unique($preparedValue));
        }

        if (!empty($textAttributeValue)) {
            $productAttributes[$attributeCode . '_value'] = array_values(array_unique($textAttributeValue));
        }

        return $productAttributes;
    }

    public function getAttributeOptions(Attribute $attribute, int $storeId = Store::DEFAULT_STORE_ID): array
    {
        return $this->attributeOptionsProvider->execute($attribute, $storeId);
    }
}
