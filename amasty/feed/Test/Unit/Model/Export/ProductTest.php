<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Test\Unit\Model\Export;

use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Export\RowCustomizer\Composite;
use Amasty\Feed\Model\InventoryResolver;
use Amasty\Feed\Test\Unit\Traits;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProductTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    public const EXPORT_RAW_DATA = [
        1 => [
            'test1' => [
                'test2' => 'test2'
            ]
        ]
    ];

    /**
     * @var Product|MockObject
     */
    private $product;

    public function setUp(): void
    {
        $this->product = $this->createPartialMock(
            Product::class,
            [
                'collectRawData',
                'prepareCatalogInventory',
                'getAttributeCollection',
                'filterAttributeCollection'
            ]
        );
        $rowCustomizer = $this->createPartialMock(
            Composite::class,
            [
                'initFromProfile',
                'prepareData'
            ]
        );

        $this->setProperty($this->product, 'rowCustomizer', $rowCustomizer, Product::class);
        $this->setProperty($this->product, '_entityCollection', 'collection', Product::class);
        $inventoryResolver = $this->createPartialMock(
            InventoryResolver::class,
            [
                'getInventoryData'
            ]
        );
        $stockItemRows = [
            1 => [
                'test_row' => 'test_row'
            ]
        ];
        $inventoryResolver->method('getInventoryData')
            ->with(array_keys(self::EXPORT_RAW_DATA))->willReturn($stockItemRows);
        $this->setProperty($this->product, 'inventoryResolver', $inventoryResolver, Product::class);
    }

    /**
     * @covers Product::getExportData
     */
    public function testGetExportData()
    {
        $this->product->expects($this->once())->method('collectRawData')
            ->willReturn(self::EXPORT_RAW_DATA);

        $multiRowData = ['customOptionsData' => ''];
        $this->setProperty($this->product, 'multirawData', $multiRowData, Product::class);

        $exportData = $this->invokeMethod($this->product, 'getExportData', []);
        $this->assertEquals([0 => ['test2' => 'test2', 'test_row' => 'test_row']], $exportData);
    }

    /**
     * @covers Product::getExportAttrCodesList
     * @dataProvider getExportAttrCodesListDataProvider
     */
    public function testGetExportAttrCodesList(string $attrCode, array $expected)
    {
        $exportAttrCodes = [
            'test_code' => 'test_code'
        ];
        $this->setProperty($this->product, 'attrCodes', $exportAttrCodes, Product::class);

        $collection = $this->createPartialMock(Collection::class, []);
        $this->product->expects($this->once())->method('getAttributeCollection')
            ->willReturn($collection);
        $attribute = $this->createPartialMock(Attribute::class, []);
        $attribute->setAttributeCode($attrCode);
        $attribute->setFrontendLabel('test_label');
        $collectionMock = $this->createMock(EavCollection::class);
        $collectionMock->method('getItems')->willReturn(['test_code' => $attribute]);
        $this->product->expects($this->once())->method('filterAttributeCollection')
            ->with($collection)->willReturn($collectionMock);

        $result = $this->product->getExportAttrCodesList();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Product::getAttributeOptions
     *
     * @dataProvider getAttributeOptionsDataProvider
     */
    public function testGetAttributeOptions($input, $values, $expected)
    {
        $options = [
            [
                'value' => $values
            ]
        ];
        $source = $this->createPartialMock(AbstractSource::class, ['getAllOptions']);
        $source->expects($this->any())->method('getAllOptions')
            ->willReturn($options);
        $attribute = $this->createPartialMock(AbstractAttribute::class, ['getSource']);
        $attribute->setFrontendInput($input);
        $attribute->expects($this->any())->method('getSource')
            ->willReturn($source);

        $result = $this->product->getAttributeOptions($attribute);
        $this->assertEquals($expected, $result);
    }

    public function getExportAttrCodesListDataProvider()
    {
        return [
            ['test_code', ['test_code' => 'test_label']],
            ['test_code2', []]
        ];
    }

    public function getAttributeOptionsDataProvider()
    {
        return [
            [
                'text', '', []
            ],
            [
                'select', 'test_value', []
            ],
            [
                'select',
                [
                    [
                        'label' => 'test_label',
                        'value' => 'test_value'
                    ]
                ],
                [
                    'test_value' => 'test_label'
                ]
            ]
        ];
    }
}
