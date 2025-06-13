<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Field;

use Amasty\Feed\Model\Config\Source\CustomFieldType;
use Amasty\Feed\Model\Export\Utils\MergedAttributeProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class CustomFieldsProcessor
{
    public const OPERATION = 0;
    public const VALUE = 1;

    /**
     * @var MergedAttributeProcessor
     */
    private $mergedAttributeProcessor;

    /**
     * @var ProductResource
     */
    private $productResource;

    public function __construct(
        MergedAttributeProcessor $mergedAttributeProcessor,
        ProductResource $productResource
    ) {
        $this->mergedAttributeProcessor = $mergedAttributeProcessor;
        $this->productResource = $productResource;
    }

    public function process(ProductInterface $product, Condition $rule): string
    {
        switch ($this->resolveAttributeType($rule)) {
            case CustomFieldType::ATTRIBUTE:
                return $this->processAttribute($product, $rule);
            case CustomFieldType::MERGED_ATTRIBUTES:
                $mergedText = $rule->getFieldResult()['merged_text'] ?? '';

                return $this->mergedAttributeProcessor->execute($product, $mergedText);
            default:
                return $rule->getFieldResult()['modify'] ?? '';
        }
    }

    private function resolveAttributeType(Condition $rule): int
    {
        $fieldResult = $rule->getFieldResult();
        if (!empty($fieldResult['merged_text'])) {
            return CustomFieldType::MERGED_ATTRIBUTES;
        }

        if (!empty($fieldResult['attribute'])) {
            return CustomFieldType::ATTRIBUTE;
        }

        return CustomFieldType::CUSTOM_TEXT;
    }

    private function processAttribute(ProductInterface $product, Condition $rule): string
    {
        $attributeValue = null;
        if (is_array($product->getData('tier_price'))
            && empty($product->getData('tier_price'))
        ) {
            $product->setData('tier_price', '');
        }

        $currentAttribute = $rule->getFieldResult()['attribute'];
        if ($product->hasData($currentAttribute)) {
            $productAttribute = $product->getData($currentAttribute);
        } else {
            $productAttribute = $this->productResource->getAttributeRawValue(
                $product->getId(),
                $currentAttribute,
                $product->getStoreId()
            );
        }

        if ($currentAttribute === 'quantity_and_stock_status') {
            $attributeValue = (int)($dataRow['qty'] ?? 0);
        } elseif ($product->getAttributeText($currentAttribute) && !is_array($productAttribute)) {
            $attributeValue = $product->getAttributeText($currentAttribute);
        } elseif (!is_array($productAttribute)) {
            $attributeValue = $productAttribute;
        }

        return $this->modifyValue($attributeValue, $rule);
    }

    /**
     * @param mixed $value
     * @param Condition $rule
     *
     * @return string
     */
    private function modifyValue($value, Condition $rule): string
    {
        if ($value) {
            $modifier = $rule->getFieldResult()['modify'] ?? '';
            if ($modifier && is_numeric($value)) {
                $value = $this->modifyNumeric($modifier, $value);
            }
        }

        return (string)$value;
    }

    /**
     * Return modified value or modifier itself if modifier does not match the pattern.
     * Modifier patterns: (+ or -)number(%).
     *
     * @param string $modifier
     * @param float|int $value
     *
     * @return float|int
     */
    private function modifyNumeric(string $modifier, $value)
    {
        $modifierArray =
            preg_split('/([\d]+([.,][\d]+)?)/', $modifier, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $modifierValue = isset($modifierArray[self::VALUE]) ? str_replace(',', '.', $modifierArray[self::VALUE]) : 0;
        if ($modifierValue && end($modifierArray) === '%') {
            $modifierValue = $value * $modifierValue / 100;
        }

        switch ($modifierArray[self::OPERATION]) {
            case '-':
                $value -= $modifierValue;
                break;
            case '+':
                $value += $modifierValue;
                break;
        }

        return $value;
    }
}
