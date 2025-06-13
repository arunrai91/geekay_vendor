<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Product\Attributes;

use Magento\CatalogImportExport\Model\Export\Product;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ImportExport\Model\Import;

class FeedAttributesProcessor
{
    public const SHIFT_OF_SEPARATOR_POSITION = 1;

    public function getAttributeValue(array $dataRow, string $code): ?string
    {
        if (isset($dataRow[$code])) {
            return (string)$dataRow[$code];
        }

        if ($this->getValueUseAdditionalAttr($dataRow, $code) && isset($dataRow[Product::COL_ADDITIONAL_ATTRIBUTES])) {
            return (string)$this->getAttrValueFromAdditionalAttr(
                (string)$dataRow[Product::COL_ADDITIONAL_ATTRIBUTES],
                $code
            );
        }

        return null;
    }

    public function getAttrValueFromCustomData(array $customData, string $type, string $code): ?string
    {
        $result = null;
        if (isset($customData[$type][$code])) {
            $result = (string)(is_array($customData[$type][$code])
                ? implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $customData[$type][$code])
                : $customData[$type][$code]);
        }

        return $result;
    }

    private function getValueUseAdditionalAttr(array $dataRow, string $code): bool
    {
        return isset($dataRow[Product::COL_ADDITIONAL_ATTRIBUTES]) &&
            strpos($dataRow[Product::COL_ADDITIONAL_ATTRIBUTES], $code . ImportProduct::PAIR_NAME_VALUE_SEPARATOR)
            !== false;
    }

    private function getAttrValueFromAdditionalAttr(string $additionalAttributesValue, string $code): ?string
    {
        $attributes = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributesValue);
        foreach ($attributes as $attribute) {
            if (strpos($attribute, $code) !== false) {
                $delimiterPosition = strpos($attribute, ImportProduct::PAIR_NAME_VALUE_SEPARATOR)
                    + self::SHIFT_OF_SEPARATOR_POSITION;

                return $delimiterPosition ? substr($attribute, $delimiterPosition) : null;
            }
        }

        return null;
    }
}
