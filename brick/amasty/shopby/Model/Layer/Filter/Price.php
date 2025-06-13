<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Api\Data\FromToFilterInterface;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterConfigResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterRequestDataResolver as DecimalFilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterSettingResolver as DecimalFilterSettingResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\Shopby\Model\Price\GetPrecisionValue;
use Amasty\Shopby\Model\Price\RemoveExtraZeros;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as PriceDataProvider;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory as PriceDataProviderFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as PriceResource;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\StoreManagerInterface;

class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price implements FromToFilterInterface
{
    public const AM_BASE_PRICE = 'am_base_price';
    public const INVALID_DATA_COUNT = 1;
    public const PRICE_DELTA = 0.01;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var PriceDataProvider
     */
    private $dataProvider;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var FilterSettingResolver
     */
    private $filterSettingResolver;

    /**
     * @var DecimalFilterRequestDataResolver
     */
    private $decimalFilterRequestDataResolver;

    /**
     * @var DecimalFilterSettingResolver
     */
    private $decimalFilterSettingResolver;

    /**
     * @var FilterConfigResolver
     */
    private $filterConfigResolver;

    /**
     * @var array
     */
    private $facetedData = null;

    /**
     * @var int
     */
    private $range = 0;

    /**
     * @var GetPrecisionValue
     */
    private $getPrecisionValue;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RemoveExtraZeros
     */
    private $removeExtraZeros;

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        PriceResource $resource,
        CustomerSession $customerSession,
        Algorithm $priceAlgorithm,
        PriceCurrencyInterface $priceCurrency,
        AlgorithmFactory $algorithmFactory,
        PriceDataProviderFactory $dataProviderFactory,
        SearchInterface $search,
        ManagerInterface $messageManager,
        FilterRequestDataResolver $filterRequestDataResolver,
        FilterSettingResolver $filterSettingResolver,
        DecimalFilterSettingResolver $decimalFilterSettingResolver,
        DecimalFilterRequestDataResolver $decimalFilterRequestDataResolver,
        FilterConfigResolver $filterConfigResolver,
        GetPrecisionValue $getPrecisionValue,
        RequestInterface $request,
        RemoveExtraZeros $removeExtraZeros,
        array $data = []
    ) {
        $this->dataProvider = $dataProviderFactory->create(['layer' => $layer]);
        $this->priceCurrency = $priceCurrency;
        $this->search = $search;
        $this->messageManager = $messageManager;
        $this->filterRequestDataResolver = $filterRequestDataResolver;
        $this->filterSettingResolver = $filterSettingResolver;
        $this->decimalFilterRequestDataResolver = $decimalFilterRequestDataResolver;
        $this->decimalFilterSettingResolver = $decimalFilterSettingResolver;
        $this->filterConfigResolver = $filterConfigResolver;
        $this->getPrecisionValue = $getPrecisionValue;
        $this->request = $request;
        $this->removeExtraZeros = $removeExtraZeros;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getFromToConfig(): array
    {
        return $this->filterConfigResolver->getConfig($this, $this->getFacetedData());
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function _getItemsData()
    {
        if ($this->filterRequestDataResolver->isHidden($this, true)) {
            return [];
        }

        $facets = $this->getFacetedData();

        $data = [];
        if (count($facets) > self::INVALID_DATA_COUNT) { // two range minimum
            foreach ($facets as $key => $aggregation) {
                $count = (int)$aggregation['count'];

                if (strpos($key, '_') === false) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count);
            }
        }

        return count($data) == self::INVALID_DATA_COUNT ? [] : $data;
    }

    private function prepareData(string $key, int $count): array
    {
        [$from, $to] = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        $from = (float) $from;

        if (!$this->range) {
            if (in_array($from, [0, '*']) && $to != '*') {
                $this->range = (float) $to;
            } elseif ($to != '*') {
                $this->range = (float) $to - $from;
            }
        }

        if ($to == '*') {
            $to = $this->range ? $from + $this->range : '';
        }
        $to = (float) $to;
        $label = $this->renderRangeLabel($from, $to);
        $filterSetting = $this->filterSettingResolver->getFilterSetting($this);
        $format = $this->getFormat($filterSetting, $from, $to);
        $value = sprintf($format, $from, $to, $this->dataProvider->getAdditionalRequestData());
        $from = $this->removeExtraZeros->execute($filterSetting, $from);
        $to = $this->removeExtraZeros->execute($filterSetting, $to);
        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }

    private function getFormat(FilterSettingInterface $filterSetting, float $from, float $to): string
    {
        $fromPrecision = $this->getPrecisionValue->execute($filterSetting, $from);
        $toPrecision = $this->getPrecisionValue->execute($filterSetting, $to);

        return '%.' . $fromPrecision . 'f-%.' . $toPrecision . 'f%s';
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        if ($this->filterRequestDataResolver->isApplied($this)) {
            return $this;
        }

        $filter = $this->filterRequestDataResolver->getFilterParam($this);
        $noValidate = false;

        if (!empty($filter) && is_string($filter)) {
            $filter = explode('-', $filter);

            $toValue = isset($filter[1]) && $filter[1] ? $filter[1] : '';
            $filter = $filter[0] . '-' . $toValue;
            $validateFilter = $this->decimalFilterRequestDataResolver->getValidFilterValue($filter);

            if (!$validateFilter) {
                $noValidate = true;
            } else {
                $this->decimalFilterRequestDataResolver->setFromTo(
                    $this,
                    (float) $validateFilter[0],
                    (float) $validateFilter[1]
                );
            }
        }

        if ($noValidate || !$filter) {
            return $this;
        }
        $this->applyFilter($validateFilter ?? $filter);

        if (!is_array($filter)) {
            $filterSetting = $this->filterSettingResolver->getFilterSetting($this);
            if ($filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER) {
                $this->getLayer()->getProductCollection()->addFieldToFilter('price', $filter);
            }
        }

        return $this;
    }

    private function applyFilter(array $filter)
    {
        list($from, $to) = $filter;

        $this->getLayer()->getProductCollection()->addFieldToFilter(
            'price',
            ['from' => $from, 'to' => $this->request->getParam('price-ranges') && $to ? $to - self::PRICE_DELTA : $to]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getFacetedData(): array
    {
        if ($this->facetedData === null) {
            $productCollection = $this->getLayer()->getProductCollection();
            try {
                $this->facetedData = $productCollection->getFacetedData(
                    $this->getAttributeModel()->getAttributeCode(),
                    $this->getSearchResult()
                );
            } catch (StateException $e) {
                if (!$this->messageManager->hasMessages()) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Make sure that "%1" attribute can be used in layered navigation',
                            $this->getAttributeModel()->getAttributeCode()
                        )
                    );
                }

                $this->facetedData = [];
            }
        }

        return $this->facetedData;
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return string|\Magento\Framework\Phrase
     */
    private function renderRangeLabel($fromPrice, $toPrice)
    {
        $delta = $this->decimalFilterRequestDataResolver->getDelta($this);
        $fromPrice = $this->decimalFilterSettingResolver->calculatePrice($this, (float) $fromPrice, $delta);

        if (!$toPrice) {
            $toPrice = 0;
        } else {
            $delta = $this->decimalFilterRequestDataResolver->getDelta($this, false);
            $toPrice = $this->decimalFilterSettingResolver->calculatePrice($this, (float) $toPrice, $delta);
        }

        return $this->renderLabelDependOnPrice((float) $fromPrice, (float) $toPrice);
    }

    /**
     * method is used for Amasty\GroupedOptions\Plugin\Shopby\Model\Layer\Filter\Price plugin
     * @param float $fromPrice
     * @param float $toPrice
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function renderLabelDependOnPrice(float $fromPrice, float $toPrice)
    {
        $settings = $this->filterSettingResolver->getFilterSetting($this);
        $fromValuePrecision = $this->getPrecisionValue->execute($settings, $fromPrice);
        $formattedFromPrice = $this->priceCurrency->format($fromPrice, true, $fromValuePrecision);

        if (!$toPrice) {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        }

        $toValuePrecision = $this->getPrecisionValue->execute($settings, $toPrice);
        $formattedToPrice = $this->priceCurrency->format($toPrice, true, $toValuePrecision);

        return __('%1 - %2', $formattedFromPrice, $formattedToPrice);
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $itemsCount = $this->decimalFilterSettingResolver->isIgnoreRanges($this) ? 0 : parent::getItemsCount();

        if ($itemsCount == 0) {
            /**
             * show up filter event don't have any option
             */
            $fromToConfig = $this->getFromToConfig();
            if ($fromToConfig && $fromToConfig['min'] != $fromToConfig['max']) {
                return 1;
            }
        }

        return $itemsCount;
    }

    private function getSearchResult(): ?SearchResultInterface
    {
        $alteredQueryResponse = null;
        if ($this->filterRequestDataResolver->hasCurrentValue($this)) {
            $searchCriteria = $this->getLayer()->getProductCollection()->getSearchCriteria([
                $this->getAttributeModel()->getAttributeCode() . '.from',
                $this->getAttributeModel()->getAttributeCode() . '.to'
            ]);
            $alteredQueryResponse = $this->search->search($searchCriteria);
        }

        return $alteredQueryResponse;
    }
}
