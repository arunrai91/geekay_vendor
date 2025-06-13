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
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\DB\Select;
use Magento\Tax\Model\Calculation;

class Taxpercents implements RowCustomizerInterface
{
    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var ExportProduct
     */
    private $export;

    /**
     * @var array
     */
    private $taxes = [];

    public function __construct(
        ExportProduct $export,
        Calculation $calculation
    ) {
        $this->export = $export;
        $this->calculation = $calculation;
    }

    public function prepareData($collection, $productIds)
    {
        $prefixOtherAttributes = FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES;
        if ($this->export->getAttributesStorage()->hasAttributes($prefixOtherAttributes)) {
            $productCollection = $this->prepareProductCollection($collection);
            $storeId = $collection->getStoreId();
            $items = $productCollection->getConnection()->fetchPairs($productCollection->getSelect());
            foreach ($items as $entityId => $taxClassId) {
                $addressRequestObject = $this->calculation->getDefaultRateRequest($storeId);
                $addressRequestObject->setProductClassId($taxClassId);
                $this->taxes[$entityId] = $this->calculation->getRate($addressRequestObject);
            }
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];

        $taxPercent = '0';
        if (isset($this->taxes[$productId]) && $this->taxes[$productId]) {
            $notForamttedTaxpercent = $this->taxes[$productId];
            $taxPercent = sprintf('%0.2f', $notForamttedTaxpercent);
        }
        $customData[FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES]['tax_percents'] = $taxPercent;

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

    private function prepareProductCollection(Collection $collection): Collection
    {
        $productCollection = clone $collection;
        $productCollection->clear();
        $productCollection->applyFrontendPriceLimitations();
        $productCollection->getSelect()->reset(Select::COLUMNS)
            ->columns(['e.entity_id', 'price_index.tax_class_id']);

        return $productCollection;
    }
}
