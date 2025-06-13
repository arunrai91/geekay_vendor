<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Test\Unit\Logging\Entity;

use Amasty\AdminActionsLog\Api\Logging\EntitySaveHandlerInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandlerProvider;
use Amasty\AdminActionsLog\Logging\Util\ClassNameNormalizer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\AdminActionsLog\Logging\Entity\SaveHandlerProvider
 */
class SaveHandlerProviderTest extends TestCase
{
    public const TEST_CLASS_NAME = 'Test\\Class\\Name';

    /**
     * @var SaveHandlerProvider
     */
    private $saveHandler;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $classNameNormalizer = $this->createMock(ClassNameNormalizer::class);
        $classNameNormalizer->expects($this->any())->method('execute')->willReturnArgument(0);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->saveHandler = $objectManager->getObject(
            SaveHandlerProvider::class,
            [
                'classNameNormalizer' => $classNameNormalizer,
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->initEntityTypesProp();
    }

    /**
     * @covers \Amasty\AdminActionsLog\Logging\Entity\SaveHandlerProvider::get
     * @dataProvider getDataProvider
     */
    public function testGet($className, $createObjectCalls, $expectedClassName)
    {
        $this->objectManagerMock->expects($this->exactly($createObjectCalls))->method('get')->willReturn(
            $this->getMockForAbstractClass(EntitySaveHandlerInterface::class)
        );

        $result = $this->saveHandler->get($className);
        $this->assertInstanceOf($expectedClassName, $result);
    }

    public function getDataProvider(): array
    {
        return [
            'no configured handler' => [
                self::TEST_CLASS_NAME . '2',
                0,
                Common::class
            ],
            'has configure handler' => [
                self::TEST_CLASS_NAME,
                1,
                EntitySaveHandlerInterface::class
            ]
        ];
    }

    private function initEntityTypesProp()
    {
        $providerReflection = new \ReflectionClass(SaveHandlerProvider::class);

        $entityTypesProp = $providerReflection->getProperty('entityTypes');
        $entityTypesProp->setAccessible(true);
        $entityTypesProp->setValue($this->saveHandler, [
            EntitySaveHandlerInterface::class => [
                'test' => self::TEST_CLASS_NAME
            ]
        ]);
    }
}
