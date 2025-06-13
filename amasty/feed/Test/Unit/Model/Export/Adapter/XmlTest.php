<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model\Export\Adapter;

use Amasty\Feed\Model\Export\Adapter\Xml;
use Amasty\Feed\Model\Export\Utils\ValueModifier;
use Amasty\Feed\Test\Unit\Traits;
use Magento\Framework\Filesystem\File\Write;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see Xml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class XmlTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const VALUE = 'test_value';
    public const MODIFIED_VALUE = 'modified_value';

    /**
     * @var Xml|MockObject
     */
    private $xml;

    /**
     * @var ValueModifier|MockObject
     */
    private $valueModifier;

    public function setUp(): void
    {
        $this->xml = $this->createPartialMock(Xml::class, []);
        $this->valueModifier = $this->createPartialMock(ValueModifier::class, ['modify', 'formatValue']);
        $this->setProperty($this->xml, 'valueModifier', $this->valueModifier, Xml::class);
    }

    /**
     * @covers Xml::writeHeader
     */
    public function testWriteHeader(): void
    {
        $header = '<created_at>{{DATE}}</created_at>';
        $this->setProperty($this->xml, 'header', $header, Xml::class);

        $fileHandler = $this->createMock(Write::class);
        $fileHandler->expects($this->once())->method('write');
        $this->setProperty($this->xml, '_fileHandler', $fileHandler, Xml::class);

        $this->xml->writeHeader();
    }

    /**
     * @covers Xml::writeFooter
     */
    public function testWriteFooter(): void
    {
        $footer = 'test_footer';
        $this->setProperty($this->xml, 'footer', $footer, Xml::class);

        $fileHandler = $this->createMock(Write::class);
        $fileHandler->expects($this->once())->method('write')->with($footer);
        $this->setProperty($this->xml, '_fileHandler', $fileHandler, Xml::class);

        $this->xml->writeFooter();
    }

    /**
     * @covers Xml::modifyValue
     * @dataProvider modifyValueDataProvider
     */
    public function testModifyValue(string $modify, string $expected): void
    {
        $field = ['modify' => $modify];
        $this->valueModifier->method('modify')
            ->with(self::VALUE, 'test_type1', 'test_arg1', 'test_arg2')
            ->willReturn(self::MODIFIED_VALUE);

        $result = $this->invokeMethod($this->xml, 'modifyValue', [$field, self::VALUE]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Xml::formatValue
     * @dataProvider formatValueDataProvider
     */
    public function testFormatValue($value, $expected): void
    {
        $field = ['modify' => 'yes'];
        $this->valueModifier->method('formatValue')->willReturn($value);

        $result = $this->invokeMethod($this->xml, 'formatValue', [$field, $value]);
        $this->assertEquals($expected, $result);
    }

    public function modifyValueDataProvider(): array
    {
        return [
            ['', self::VALUE],
            ['test_type1:test_arg1^test_arg2', self::MODIFIED_VALUE]
        ];
    }

    public function formatValueDataProvider(): array
    {
        return [
            [1, 1],
            ['test', '<![CDATA[test]]>']
        ];
    }
}
