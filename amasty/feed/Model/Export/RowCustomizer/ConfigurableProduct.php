<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\ConfigurableImportExport\Model\Export\RowCustomizer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class ConfigurableProduct implements RowCustomizerInterface
{
    /**
     * @var array
     */
    protected $configurableData = [];

    public function prepareData($collection, $productIds)
    {
        $productCollection = clone $collection;
        $productCollection->clear();
        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToFilter('type_id', ['eq' => ConfigurableProductType::TYPE_CODE]);

        while ($product = $productCollection->fetchItem()) {
            $productAttributesOptions = $product->getTypeInstance()->getConfigurableOptions($product);
            $this->configurableData[$product->getId()] = [];

            $variations = [];
            foreach ($productAttributesOptions as $attributeId => $productAttributeOption) {
                foreach ($productAttributeOption as $optValues) {
                    $variations[$optValues['sku']][$attributeId] = $optValues['value_index'];
                }
            }
            $this->configurableData[$product->getId()] = [
                RowCustomizer::CONFIGURABLE_VARIATIONS_COLUMN => $variations
            ];
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        if (!empty($this->configurableData[$productId])) {
            $dataRow = array_merge($dataRow, $this->configurableData[$productId]);
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
}
