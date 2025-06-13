<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Test\Unit\Logging\Util;

use Amasty\AdminActionsLog\Logging\Util\ClassNameNormalizer;
use Amasty\AdminActionsLog\Logging\Util\DetailsBuilder;
use Amasty\AdminActionsLog\Model\LogEntry\LogDetail;
use Amasty\AdminActionsLog\Model\LogEntry\LogDetailFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\AdminActionsLog\Logging\Util\DetailsBuilder
 */
class DetailsBuilderTest extends TestCase
{
    public const MODEL_NAME = 'Test\\Model\\Name';

    /**
     * @var DetailsBuilder
     */
    private $detailsBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $classNameNormalizer = $this->createMock(ClassNameNormalizer::class);
        $classNameNormalizer->expects($this->any())->method('execute')->willReturnArgument(0);

        $detailFactory = $this->createMock(LogDetailFactory::class);
        $detailFactory->expects($this->any())->method('create')->willReturnCallback(function ($data) {
            return $this->createConfiguredMock(
                LogDetail::class,
                [
                    'getData' => $data['data'] ?? []
                ]
            );
        });

        $this->detailsBuilder = $objectManager->getObject(
            DetailsBuilder::class,
            [
                'classNameNormalizer' => $classNameNormalizer,
                'detailFactory' => $detailFactory
            ]
        );
    }

    /**
     * @covers \Amasty\AdminActionsLog\Logging\Util\DetailsBuilder::build
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $beforeData, array $afterData, array $expected)
    {
        $result = $this->detailsBuilder->build(self::MODEL_NAME, $beforeData, $afterData);

        $result = array_map(function ($detail) {
            return $detail->getData();
        }, $result);
        $this->assertEquals($expected, $result);
    }

    public function buildDataProvider(): array
    {
        return [
            'no diff' => [
                ['test1' => 'value1', 'test2' => 'value2'],
                ['test1' => 'value1', 'test2' => 'value2'],
                []
            ],
            'one diff' => [
                ['test1' => 'value1', 'test2' => 'value2',],
                ['test1' => 'value11', 'test2' => 'value2'],
                [$this->prepareExpectedDetail('test1', 'value1', 'value11')]
            ],
            'value added' => [
                ['test1' => 'value1'],
                ['test1' => 'value1', 'test2' => 'value2'],
                [$this->prepareExpectedDetail('test2', null, 'value2')]
            ],
            'value deleted' => [
                ['test1' => 'value1', 'test2' => 'value2'],
                ['test1' => 'value1'],
                [$this->prepareExpectedDetail('test2', 'value2', null)]
            ]
        ];
    }

    private function prepareExpectedDetail(string $name, ?string $oldValue, ?string $newValue): array
    {
        return [
            LogDetail::MODEL => self::MODEL_NAME,
            LogDetail::NAME => $name,
            LogDetail::OLD_VALUE => $oldValue,
            LogDetail::NEW_VALUE => $newValue
        ];
    }
}
