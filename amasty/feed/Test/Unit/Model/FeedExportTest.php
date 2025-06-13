<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model;

use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Export\ProductFactory;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\FeedExport;
use Amasty\Feed\Model\FeedRepository;
use Amasty\Feed\Test\Unit\Traits;
use Magento\Framework\Event\Manager;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see FeedExport
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FeedExportTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const STORE_ID = 1;
    public const FILENAME = 'test';
    public const UTM_PARAMS = [];
    public const FORMAT_PRICE_CURRENCY = 'test_currency';
    public const FORMAT_PRICE_CURRENCY_SHOW = 'test_currency_show';
    public const FORMAT_PRICE_DECIMALS = 'test_price_decimals';
    public const FORMAT_PRICE_DECIMAL_POINT = 'test_decimal_point';
    public const FORMAT_PRICE_SEPARATOR = 'test_separator';
    public const WRITER = 'test_writer';
    public const ATTRIBUTES = 'test_attrs';
    public const PARENT_ATTRIBUTES = 'test_parent_attrs';
    public const EXPORT = 'test_export';

    /**
     * @var FeedExport|MockObject
     */
    private $feedExport;

    /**
     * @var AbstractAdapter
     */
    private $abstractAdapter;

    public function setUp(): void
    {
        $this->feedExport = $this->createPartialMock(FeedExport::class, []);
    }

    /**
     * @covers FeedExport::processingCsv
     * @dataProvider processingAttributesDataProvider
     */
    public function testPrepareCsvAttributes($parent, $attribute, $expected)
    {
        $feedField = [
            [
            'parent' => 'yes',
            'attribute' => $attribute
            ]
        ];
        $feed = $this->initFeedMock();
        $feed->expects($this->once())->method('getCsvField')
            ->willReturn($feedField);
        $attributes = [
            'test1' => [
                'test2' => ''
            ]
        ];
        $this->invokeMethod($this->feedExport, 'prepareCsvAttributes', [$feed, &$attributes, $parent]);
        $this->assertEquals($expected, $attributes);
    }

    /**
     * @covers FeedExport::processingXml
     * @dataProvider processingAttributesDataProvider
     */
    public function testPrepareXmlAttributes($parent, $attribute, $expected)
    {
        $parent = false;
        $xmlContent = '#{/attribute="' . $attribute . '"/parent="yes"}#';
        $feed = $this->initFeedMock();
        $feed->expects($this->once())->method('getXmlContent')
            ->willReturn($xmlContent);
        $attributes = [
            'test1' => [
                'test2' => ''
            ]
        ];

        $this->invokeMethod($this->feedExport, 'prepareXmlAttributes', [$feed, &$attributes, $parent]);
        $this->assertEquals($expected, $attributes);
    }

    /**
     * @return Feed|MockObject
     */
    private function initFeedMock(): Feed
    {
        return $this->createConfiguredMock(
            Feed::class,
            [
                'getStoreId' => self::STORE_ID,
                'getFilename' => self::FILENAME,
                'getFormatPriceCurrency' => self::FORMAT_PRICE_CURRENCY,
                'getFormatPriceCurrencyShow' => self::FORMAT_PRICE_CURRENCY_SHOW,
                'getFormatPriceDecimals' => self::FORMAT_PRICE_DECIMALS,
                'getFormatPriceDecimalPoint' => self::FORMAT_PRICE_DECIMAL_POINT,
                'getFormatPriceThousandsSeparator' => self::FORMAT_PRICE_SEPARATOR
            ]
        );
    }

    public function processingAttributesDataProvider(): array
    {
        return [
            [false, 'test1|test2', ['test1' => ['test2' => 'test2']]],
            [false, 'test2|test3', ['test1' => ['test2' => '']]],
            [true, 'test1|test2', ['test1' => ['test2' => 'test2']]],
            [true, 'test2|test3', ['test1' => ['test2' => '']]]
        ];
    }
}
