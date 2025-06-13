<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter;

use Amasty\Shopby\Model\Layer\Filter\Decimal;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterConfigResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterSettingResolver as DecimalFilterSettingResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\Shopby\Model\Price\GetPrecisionValue;
use Amasty\Shopby\Model\Price\RemoveExtraZeros;
use Amasty\Shopby\Test\Unit\Traits;
use Amasty\ShopbyBase\Model\FilterSetting;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DecimalTest
 *
 * @see Decimal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DecimalTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Decimal
     */
    private $model;

    /**
     * @var MockObject|FilterSettingResolver
     */
    private $filterSettingResolver;

    /**
     * @var MockObject|FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var MockObject|FilterConfigResolver
     */
    private $decimalFilterConfigResolver;

    /**
     * @var MockObject|DecimalFilterSettingResolver
     */
    private $decimalFilterSettingResolver;

    /**
     * @var MockObject|\Amasty\Shopby\Model\ResourceModel\Fulltext\Collection
     */
    private $productCollection;

    /**
     * @var GetPrecisionValue|MockObject
     */
    private $getPrecisionValue;

    /**
     * @var FilterSetting|MockObject
     */
    private $settingFilter;

    /**
     * @var RemoveExtraZeros
     */
    private $removeExtraZeros;

    public function setup(): void
    {
        $this->removeExtraZeros = $this->createMock(RemoveExtraZeros::class);
        $this->settingFilter = $this->createMock(FilterSetting::class);
        $this->getPrecisionValue = $this->createMock(GetPrecisionValue::class);
        $this->filterSettingResolver = $this->createMock(FilterSettingResolver::class);
        $this->filterRequestDataResolver = $this->createMock(FilterRequestDataResolver::class);
        $this->decimalFilterSettingResolver = $this->createMock(DecimalFilterSettingResolver::class);
        $this->decimalFilterConfigResolver = $this->createMock(FilterConfigResolver::class);
        $filterItemFactory = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\ItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filterItem = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item::class)
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeModel = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attributeModel->expects($this->any())->method('getAttributeCode')->willReturn('test');
        $search = $this->createMock(\Magento\Search\Api\SearchInterface::class);
        $layer = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $this->productCollection = $this->createMock(\Amasty\Shopby\Model\ResourceModel\Fulltext\Collection::class);
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $searchResult = $this->createMock(SearchResultInterface::class);
        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $currency = $this->createMock(Currency::class);
        $messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['hasMessages', 'addErrorMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $filterItemFactory->expects($this->any())->method('create')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setFilter')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setLabel')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setValue')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setValue')->willReturn($filterItem);
        $search->expects($this->any())->method('search')->willReturn($searchResult);
        $layer->expects($this->any())->method('getProductCollection')->willReturn($this->productCollection);
        $this->productCollection->expects($this->any())->method('getSearchCriteria')->willReturn($searchCriteria);
        $messageManager->expects($this->any())->method('hasMessages')->willReturn(true);
        $messageManager->expects($this->any())->method('addErrorMessage')->willReturn(true);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);
        $priceCurrency->expects($this->any())->method('getCurrency')->willReturn($currency);

        $this->filterSettingResolver->expects($this->any())
            ->method('getFilterSetting')
            ->willReturn($this->settingFilter);

        $currency->expects($this->any())
            ->method('formatPrecision')
            ->willReturnCallback(
                function ($price, $precision) {
                    return number_format($price, $precision);
                }
            );

        $this->model = $this->getObjectManager()->getObject(
            Decimal::class,
            [
                'filterSettingResolver' => $this->filterSettingResolver,
                'filterRequestDataResolver' => $this->filterRequestDataResolver,
                'decimalConfigResolver' => $this->decimalFilterConfigResolver,
                'decimalFilterSettingResolver' => $this->decimalFilterSettingResolver,
                'filterItemFactory' => $filterItemFactory,
                'search' => $search,
                'messageManager' => $messageManager,
                'priceCurrency' => $priceCurrency,
                'getPrecisionValue' => $this->getPrecisionValue,
                'removeExtraZeros' => $this->removeExtraZeros
            ]
        );

        $this->model->setAttributeModel($attributeModel);
        $this->setProperty($this->model, '_catalogLayer', $layer);
    }

    /**
     * @covers Decimal::getItemsCountIfNotIgnoreRanges
     */
    public function testGetItemsCountIfNotIgnoreRanges()
    {
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $this->filterSettingResolver->expects($this->any())->method('getFilterSetting')->willReturn($settingFilter);
        $this->setProperty($this->model, 'facetedData', ['10_20' => ['count' => 2]]);

        $this->assertEquals(1, $this->model->getItemsCount());
    }

    /**
     * @covers Decimal::getItemsCountIfIgnoreRanges
     */
    public function testGetItemsCountIfIgnoreRanges()
    {
        $data = [
            'data' =>['count' => 1, 'min' => 1, 'max' => 2],
            '10_20' => ['count' => 2]
        ];
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setDisplayMode(3);
        $this->filterSettingResolver->expects($this->any())->method('getFilterSetting')->willReturn($settingFilter);
        $this->decimalFilterConfigResolver->expects($this->any())->method('getConfig')
            ->willReturnOnConsecutiveCalls($this->returnValue([]), $this->returnValue(['min' => 1, 'max' => 2]));

        $this->setProperty($this->model, 'facetedData', ['data' =>['count' => 1, 'min' => 0, 'max' => 0]]);
        $this->assertEquals(0, $this->model->getItemsCount());

        $this->setProperty($this->model, 'facetedData', $data);
        $this->assertEquals(1, $this->model->getItemsCount());
    }


    /**
     * @covers Decimal::getItemsData
     * @dataProvider getItemsDataProvider
     *
     * @param int|float $from
     * @param int|float $to
     */
    public function testGetItemsData(array $data, bool $isHideZeros, string $rangeLabel, $from, $to): void
    {
        $this->setProperty($this->model, 'facetedData', ['data' => 1]);
        $this->assertEquals([], $this->invokeMethod($this->model, '_getItemsData'));
        $settingFilter = $this->createMock(FilterSetting::class);
        $this->removeExtraZeros->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($filterSetting, $value) use ($isHideZeros) {
                    return $isHideZeros && (int)$value == $value ? (int)$value : $value;
                }
            );
        $this->getPrecisionValue->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($filterSetting, $value) use ($isHideZeros) {
                    return $isHideZeros && (int)$value == $value ? 0 : PriceCurrencyInterface::DEFAULT_PRECISION;
                }
            );
        $settingFilter->expects($this->any())->method('getUnitsLabelUseCurrencySymbol')->willReturn(true);
        $this->filterSettingResolver->expects($this->any())->method('getFilterSetting')->willReturn($settingFilter);

        $this->setProperty($this->model, 'facetedData', $data);
        $result = $this->invokeMethod($this->model, '_getItemsData');
        $this->assertEquals($rangeLabel, $result[0]['value']);
        $this->assertEquals('2', $result[0]['count']);
        $this->assertEquals($from, $result[0]['from']);
        $this->assertEquals($to, $result[0]['to']);
    }

    /**
     * Data provider for getItemsData test
     */
    public function getItemsDataProvider(): array
    {
        return [
            [
                ['data' =>['count' => 1, 'min' => 1, 'max' => 2], '10_20' => ['count' => 2]],
                false,
                '10.00-20.00',
                10.00,
                20.00
            ],
            [
                ['data' =>['count' => 1, 'min' => 1, 'max' => 2], '10_20' => ['count' => 2]],
                true,
                '10-20',
                10,
                20
            ]
        ];
    }

    /**
     * @covers Decimal::getSearchResult
     */
    public function testGetSearchResult()
    {
        $this->assertNull($this->invokeMethod($this->model, 'getSearchResult'));
        $this->filterRequestDataResolver->expects($this->any())->method('hasCurrentValue')->willReturn(true);
        $this->assertInstanceOf(SearchResultInterface::class, $this->invokeMethod($this->model, 'getSearchResult'));
    }

    /**
     * @covers Decimal::getFacetedData
     */
    public function testGetFacetedData()
    {
        $this->setProperty($this->model, 'magentoVersion', '2.4.2');
        $this->productCollection->expects($this->any())->method('getFacetedData')->willReturn(['test1', 'test2']);
        $this->assertEquals(['test1', 'test2'], $this->invokeMethod($this->model, 'getFacetedData'));

        $this->setProperty($this->model, 'facetedData', ['test']);
        $this->assertEquals(['test'], $this->invokeMethod($this->model, 'getFacetedData'));
    }

    /**
     * @covers Decimal::getFacetedDataException
     */
    public function testGetFacetedDataException()
    {
        $this->setProperty($this->model, 'magentoVersion', '2.4.2');
        $this->productCollection->expects($this->any())->method('getFacetedData')
            ->willThrowException(new \Magento\Framework\Exception\StateException(__('exceprion')));
        $this->assertEquals([], $this->invokeMethod($this->model, 'getFacetedData'));
    }

    /**
     * @covers Decimal::getDefaultRangeLabel
     * @dataProvider getDefaultRangeLabelProvider
     *
     * @param bool $unitsLabelUseCurrencySymbol
     * @param float $fromPrice
     * @param float $toPrice
     * @param string $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testGetDefaultRangeLabel(
        bool $unitsLabelUseCurrencySymbol,
        bool $isHideZeros,
        float $fromPrice,
        float $toPrice,
        string $expectedResult
    ): void {
        $this->settingFilter->expects($this->any())
            ->method('getUnitsLabelUseCurrencySymbol')
            ->willReturn($unitsLabelUseCurrencySymbol);
        $this->getPrecisionValue->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($settingFilter, $price) use ($isHideZeros) {
                    return $isHideZeros && (int)$price == $price
                        ? 0
                        : PriceCurrencyInterface::DEFAULT_PRECISION;
                }
            );

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethod($this->model, 'getDefaultRangeLabel', [$fromPrice, $toPrice])
        );
    }

    /**
     * Data provider for getDefaultRangeLabel test
     * @return array
     */
    public function getDefaultRangeLabelProvider(): array
    {
        return [
            [true, true, 10.0, 20.0, '10 - 20'],
            [true, false, 10.0, 20.0, '10.00 - 20.00'],
            [false, false, 10.0, 20.0, '']
        ];
    }

    /**
     * @covers Decimal::getRangeLabel
     * @dataProvider getRangeForStateProvider
     *
     * @param int $position
     * @param bool $isHideZeros
     * @param float $fromPrice
     * @param float $toPrice
     * @param string $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testGetRangeLabel(
        int $position,
        bool $isHideZeros,
        float $fromPrice,
        float $toPrice,
        string $expectedResult
    ): void {
        $this->getPrecisionValue->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($settingFilter, $price) use ($isHideZeros) {
                    return $isHideZeros && (int)$price == $price
                        ? 0
                        : PriceCurrencyInterface::DEFAULT_PRECISION;
                }
            );

        $this->settingFilter->expects($this->any())
            ->method('getPositionLabel')
            ->willReturn($position);
        $this->settingFilter->expects($this->any())
            ->method('getUnitsLabel')
            ->willReturn('$');

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethod($this->model, 'getRangeLabel', [$fromPrice, $toPrice])
        );
    }

    /**
     * Data provider for getRangeForState test
     * @return array
     */
    public function getRangeForStateProvider(): array
    {
        return [
            [0, true, 10.0, 20.0, '$10 - $20'],
            [1, false, 10.0, 20.0, '10.00$ - 20.00$']
        ];
    }

    /**
     * @covers Decimal::formatLabelForStateAndRange
     * @dataProvider formatLabelForStateAndRangeProvider
     *
     * @param int $position
     * @param bool $isHideZeros
     * @param float $price
     * @param string $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testFormatLabelForStateAndRange(
        int $position,
        bool $isHideZeros,
        float $price,
        string $expectedResult
    ): void {
        $this->getPrecisionValue->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($settingFilter, $price) use ($isHideZeros) {
                    return $isHideZeros && (int)$price == $price
                        ? 0
                        : PriceCurrencyInterface::DEFAULT_PRECISION;
                }
            );

        $this->settingFilter->expects($this->any())
            ->method('getPositionLabel')
            ->willReturn($position);
        $this->settingFilter->expects($this->any())
            ->method('getUnitsLabel')
            ->willReturn('$');

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethod($this->model, 'formatLabelForStateAndRange', [$price])
        );
    }

    /**
     * Data provider for formatLabelForStateAndRange test
     * @return array
     */
    public function formatLabelForStateAndRangeProvider(): array
    {
        return [
            [0, false, 10.0, '$10.00'],
            [1, true, 20.0, '20$']
        ];
    }
}
