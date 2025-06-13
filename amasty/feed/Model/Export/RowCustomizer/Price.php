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
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice as CatalogRegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice as CatalogSpecialPrice;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Price implements RowCustomizerInterface
{
    /**
     * @var array
     */
    private $prices = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Product
     */
    private $export;

    public function __construct(
        StoreManagerInterface $storeManager,
        Product $export
    ) {
        $this->storeManager = $storeManager;
        $this->export = $export;
    }

    public function prepareData($collection, $productIds)
    {
        if ($this->export->getAttributesStorage()->hasAttributes(FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE)) {
            $productCollection = clone $collection;
            $productCollection->clear();
            $productCollection->applyFrontendPriceLimitations();
            $productCollection->addAttributeToSelect([
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'price_type', // necessary for correct calculation of prices
                'tax_class_id'
            ]);
            $productCollection->getSelect()->columns(['maximal_price' => 'price_index.max_price']);

            $storeId = $collection->getStoreId() === Store::DEFAULT_STORE_ID
                ? $this->storeManager->getDefaultStoreView()->getId() // For getting valid configurable product price
                : $collection->getStoreId();

            $this->storeManager->setCurrentStore($storeId);
            $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
            // For parent product FormatPriceCurrency could be null.
            if ($feedProfileCurrency = $this->export->getFeedProfile()->getFormatPriceCurrency()) {
                $currentCurrency->setCurrencyCode($feedProfileCurrency);
            }
            $this->storeManager->getStore()->setCurrentCurrencyCode($feedProfileCurrency);

            foreach ($productCollection->getItems() as $item) {
                $this->processItemPrices($item);
            }
            $this->storeManager->getStore()->setCurrentCurrency($currentCurrency);
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];
        $customData[FeedAttributesStorage::PREFIX_PRICE_ATTRIBUTE] = $this->prices[$productId] ?? [];

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

    private function processItemPrices(ProductInterface $item): void
    {
        $specialPriceInfo = $item->getPriceInfo()->getPrice(CatalogSpecialPrice::PRICE_CODE);
        $finalPriceInfo = $item->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE);
        $priceInfo = $item->getPriceInfo();

        $regularPriceAmount = $priceInfo->getPrice(CatalogRegularPrice::PRICE_CODE)->getAmount();
        $specialPrice = $specialPriceInfo->getValue();
        $finalPrice = $finalPriceInfo->getAmount()->getValue(['tax', 'weee']);
        $finalTaxPrice = $finalPriceInfo->getAmount()->getValue();
        $regularPrice = $priceInfo->getPrice(CatalogRegularPrice::PRICE_CODE)->getValue();
        $groupedPrice = 0;

        switch ($item->getTypeId()) {
            case BundleProductType::TYPE_CODE:
                // in case of bundle product we must recalculate some prices manually
                if ($item->getTypeId() === BundleProductType::TYPE_CODE) {
                    if ($item->getPrice()) {//fixed price
                        $percentage = $priceInfo->getPrice(CatalogSpecialPrice::PRICE_CODE)->getDiscountPercent();
                        if ($percentage) { //special price
                            $finalPrice = $specialPrice = $regularPriceAmount->getBaseAmount() * $percentage / 100;
                            $finalTaxPrice = $regularPriceAmount->getValue() * $percentage / 100;
                        }
                    } else {//dynamic price
                        $finalPrice = $finalPriceInfo->getMinimalPrice()->getBaseAmount();
                        if ($specialPrice < 0.0001 && $specialPrice !== false) {
                            $specialPrice = $finalPrice;
                        }
                    }
                }
                break;
            case Grouped::TYPE_CODE:
                foreach ($item->getTypeInstance()->getAssociatedProducts($item) as $childProduct) {
                    $childFinalPriceInfo = $childProduct->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE);
                    $groupedPrice += $childFinalPriceInfo->getAmount()->getValue(['tax', 'weee']);
                }
                break;
        }

        $this->prices[$item['entity_id']] = [
            'price' => $regularPriceAmount->getValue(['tax','weee']),
            'tax_price' => $regularPriceAmount->getValue(),
            'regular_price' => $regularPrice,
            'final_price' => $finalPrice,
            'tax_final_price' => $finalTaxPrice,
            'min_price' => $finalPriceInfo->getMinimalPrice()->getValue(['tax', 'weee']),
            'max_price' => $finalPriceInfo->getMaximalPrice()->getValue(['tax', 'weee']),
            'tax_min_price' => $finalPriceInfo->getMinimalPrice()->getValue(),
            'special_price' => $specialPrice,
            'grouped_price' => $groupedPrice ?: $finalPrice
        ];
    }
}
