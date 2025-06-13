<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product\Attributes\FeedAttributesStorage;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class Effectivedate implements RowCustomizerInterface
{
    public const DS = DIRECTORY_SEPARATOR;
    public const START_UNIX_DATE = '1978-01-01T00:00';
    public const END_UNIX_DATE = '2038-01-01T00:00';
    public const SALE_PRICE_EFFECITVEDATE_INDEX = 'sale_price_effective_date';

    /**
     * @var StoreManagerInterface
     */
    private $timezone;

    /**
     * @var string[]
     */
    private $effectiveDates = [];

    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    public function prepareData($collection, $productIds)
    {
        $productCollection = $this->prepareProductCollection($collection);
        foreach ($productCollection as $item) {
            $specialFromDate = $item->getSpecialFromDate();
            $specialToDate = $item->getSpecialToDate();
            if ($specialFromDate || $specialToDate) {
                $this->effectiveDates[$item->getId()] = $this->getSpecialEffectiveDate(
                    $specialFromDate,
                    $specialToDate
                );
            }
        }

        return $this;
    }

    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow[Composite::CUSTOM_DATA_KEY];
        if (isset($this->effectiveDates[$productId])) {
            $customData[FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES] = [
                self::SALE_PRICE_EFFECITVEDATE_INDEX => $this->effectiveDates[$productId]
            ];
        } else {
            $customData[FeedAttributesStorage::PREFIX_OTHER_ATTRIBUTES] = [
                self::SALE_PRICE_EFFECITVEDATE_INDEX => ""
            ];
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

    private function prepareProductCollection(Collection $collection): Collection
    {
        $productCollection = clone $collection;
        $productCollection->clear();
        $productCollection->applyFrontendPriceLimitations();
        $productCollection->addAttributeToSelect([
            'price',
            'special_price',
            'special_from_date',
            'special_to_date'
        ]);
        $productCollection->addAttributeToFilter('special_price', ['notnull' => true]);

        return $productCollection;
    }

    private function getSpecialEffectiveDate(?string $specialFromDate, ?string $specialToDate): string
    {
        return $this->getSpecialFromDate($specialFromDate) . self::DS . $this->getSpecialToDate($specialToDate);
    }

    private function getSpecialFromDate(?string $specialFromDate = null): string
    {
        $timeZoneValue = $this->timezone->getConfigTimezone();
        $timeZone = new \DateTimeZone($timeZoneValue);
        $dateValue = new \DateTime(self::START_UNIX_DATE, $timeZone);

        if ($specialFromDate) {
            $dateValue = new \DateTime($specialFromDate, $timeZone);
        }

        return $dateValue->format('Y-m-d\TH:iP');
    }

    private function getSpecialToDate(?string $specialToDate = null): string
    {
        $timeZoneValue = $this->timezone->getConfigTimezone();
        $timeZone = new \DateTimeZone($timeZoneValue);
        $dateValue = new \DateTime(self::END_UNIX_DATE, $timeZone);

        if ($specialToDate) {
            $dateValue = new \DateTime($specialToDate, $timeZone);
        }

        return $dateValue->format('Y-m-d\TH:iP');
    }
}
