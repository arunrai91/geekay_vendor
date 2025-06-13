<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter;

use Amasty\Shopby\Model\Layer\Filter\Price;
use Amasty\Shopby\Model\Price\RemoveExtraZeros;
use Amasty\Shopby\Test\Unit\Traits;
use Magento\Store\Api\Data\StoreInterface;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterSettingResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\FilterRequestDataResolver;
use Amasty\Shopby\Model\Layer\Filter\Resolver\Decimal\FilterSettingResolver as DecimalFilterSettingResolver;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PriceTest
 *
 * @see Price
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Price
     */
    private $model;

    /**
     * @var MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * @var MockObject|\Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var MockObject|\Magento\Framework\Registry
     */
    private $storeMock;

    /**
     * @var MockObject|\Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var MockObject|FilterSettingResolver
     */
    private $filterSettingResolver;

    /**
     * @var MockObject|FilterRequestDataResolver
     */
    private $filterRequestDataResolver;

    /**
     * @var MockObject|DecimalFilterSettingResolver
     */
    private $decimalFilterSettingResolver;

    /**
     * @var RemoveExtraZeros
     */
    private $removeExtraZeros;

    public function setup(): void
    {
        $this->removeExtraZeros = $this->createMock(RemoveExtraZeros::class);
        $filterItemFactory = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\ItemFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filterItem = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item::class)
            ->addMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeModel = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $searchEngine = $this->createMock(\Magento\Search\Model\SearchEngine::class);
        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->filterRequestDataResolver = $this->createMock(FilterRequestDataResolver::class);
        $this->filterSettingResolver = $this->createMock(FilterSettingResolver::class);
        $this->decimalFilterSettingResolver = $this->createMock(DecimalFilterSettingResolver::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->dataProvider = $this->createMock(\Magento\Catalog\Model\Layer\Filter\DataProvider\Price::class);
        $dataProviderFactory = $this->createMock(\Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(['getId'])
            ->addMethods(['getCurrentCurrencyRate'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->addMethods(['hasMessages',])
            ->onlyMethods(['addErrorMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $filterItemFactory->expects($this->any())->method('create')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setFilter')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setLabel')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setValue')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setValue')->willReturn($filterItem);
        $searchEngine->expects($this->any())->method('search')->willReturn(true);
        $messageManager->expects($this->any())->method('hasMessages')->willReturn(true);
        $messageManager->expects($this->any())->method('addErrorMessage')->willReturn(true);
        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->dataProvider->expects($this->any())->method('getAdditionalRequestData')->willReturn(10);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);
        $dataProviderFactory->expects($this->any())->method('create')->willReturn($this->dataProvider);

        $this->model = $this->getObjectManager()->getObject(
            Price::class,
            [
                'filterItemFactory' => $filterItemFactory,
                'searchEngine' => $searchEngine,
                'messageManager' => $messageManager,
                'priceCurrency' => $priceCurrency,
                'scopeConfig' => $this->scopeConfig,
                'coreRegistry' => $this->coreRegistry,
                '_storeManager' => $storeManager,
                'dataProviderFactory' => $dataProviderFactory,
                'filterSettingResolver' => $this->filterSettingResolver,
                'filterRequestDataResolver' => $this->filterRequestDataResolver,
                'decimalFilterSettingResolver' => $this->decimalFilterSettingResolver,
                'removeExtraZeros' => $this->removeExtraZeros
            ]
        );

        $this->model->setAttributeModel($attributeModel);
    }

    /**
     * @covers \Amasty\Shopby\Model\Layer\Filter\Price::prepareData
     * @dataProvider prepareDataProvider
     */
    public function testPrepareData(bool $isHideZeros, $from, $to)
    {
        $this->removeExtraZeros->expects($this->any())
            ->method('execute')
            ->willReturnCallback(
                function ($filterSetting, $value) use ($isHideZeros) {
                    return $isHideZeros && (int)$value == $value ? (int)$value : $value;
                }
            );
        $result = $this->invokeMethod($this->model, 'prepareData', ['10_20', 5]);
        $this->assertEquals(5, $result['count']);
        $this->assertEquals($from, $result['from']);
        $this->assertEquals($to, $result['to']);
    }

    /**
     * Data provider for prepareData test
     */
    public function prepareDataProvider(): array
    {
        return [
            [false, 10.00, 20.00],
            [true, 10, 20]
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetSearchResult()
    {
        $this->assertNull($this->invokeMethod($this->model, 'getSearchResult'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderRangeLabel()
    {
        $this->decimalFilterSettingResolver->expects($this->any())->method('calculatePrice')
            ->willReturnOnConsecutiveCalls(10, 20, 10, 10, 10);
        $this->assertEquals(
            '10 - 20',
            (string)$this->invokeMethod($this->model, 'renderRangeLabel', [10, 20])
        );
        $this->assertEquals(
            '10 and above',
            (string)$this->invokeMethod($this->model, 'renderRangeLabel', [10, ''])
        );
        $this->dataProvider->expects($this->any())->method('getOnePriceIntervalValue')->willReturn(true);
        $this->assertEquals(
            '10',
            (string)$this->invokeMethod($this->model, 'renderRangeLabel', [10, 10])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderRangeLabelWithDifferenceRate()
    {
        $this->storeMock->expects($this->any())->method('getCurrentCurrencyRate')->willReturn(2);
        $this->assertEquals(
            '20 - 39.99',
            (string)$this->invokeMethod($this->model, '_renderRangeLabel', [10, 20])
        );
    }
}
