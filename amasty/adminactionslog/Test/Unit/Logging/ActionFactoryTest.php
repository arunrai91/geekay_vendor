<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Test\Unit\Logging;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Logging\ActionFactory;
use Amasty\AdminActionsLog\Logging\ActionType\CompositeFactory;
use Amasty\AdminActionsLog\Logging\ActionType\Dummy;
use Amasty\AdminActionsLog\Logging\ActionType\HandlerResolver;
use Amasty\AdminActionsLog\Logging\Metadata;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\AdminActionsLog\Logging\ActionFactory
 */
class ActionFactoryTest extends TestCase
{
    public const REQUEST_ACTION_NAME = 'test_action';
    public const METADATA_EVENT_NAME = 'test_event_name';

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var HandlerResolver|MockObject
     */
    private $actionHandlerResolver;

    /**
     * @var CompositeFactory|MockObject
     */
    private $compositeFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->actionHandlerResolver = $this->createPartialMock(
            HandlerResolver::class,
            ['getHandlers', 'getValidators']
        );
        $this->compositeFactory = $this->createPartialMock(
            CompositeFactory::class,
            ['create']
        );
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class
        );

        $this->actionFactory = $objectManager->getObject(
            ActionFactory::class,
            [
                'actionHandlerResolver' => $this->actionHandlerResolver,
                'compositeFactory' => $this->compositeFactory,
                'objectManager' => $this->objectManager,
                'dummyHandlerClass' => Dummy::class
            ]
        );
    }

    public function testCreate()
    {
        $metadata = $this->getMetadataMock();
        $loggingAction = $this->createMock(LoggingActionInterface::class);
        $this->actionHandlerResolver->expects($this->any())->method('getHandlers')
            ->with(...[self::REQUEST_ACTION_NAME, self::METADATA_EVENT_NAME])
            ->willReturn([LoggingActionInterface::class]);
        $this->objectManager->expects($this->any())->method('create')
            ->with(...[LoggingActionInterface::class, ['metadata' => $metadata]])
            ->willReturn($loggingAction);
        $this->compositeFactory->expects($this->any())->method('create')
            ->with(...[['actions' => [$loggingAction]]])
            ->willReturn($loggingAction);

        $result = $this->actionFactory->create($metadata, ActionFactory::FETCH_ANY);
        $this->assertEquals($result, $loggingAction);
    }

    /**
     * @covers \Amasty\AdminActionsLog\Logging\ActionFactory::create
     */
    public function testCreateNoHandlers()
    {
        $metadata = $this->getMetadataMock();
        $dummyLoggingAction = $this->createMock(Dummy::class);
        $this->objectManager->expects($this->any())->method('create')
            ->with(...[Dummy::class, ['metadata' => $metadata]])
            ->willReturn($dummyLoggingAction);
        $this->compositeFactory->expects($this->any())->method('create')
            ->with(...[['actions' => [$dummyLoggingAction]]])
            ->willReturn($dummyLoggingAction);

        $result = $this->actionFactory->create($metadata);
        $this->assertEquals($result, $dummyLoggingAction);
    }

    /**
     * @return Metadata|MockObject
     */
    private function getMetadataMock()
    {
        return $this->createConfiguredMock(
            Metadata::class,
            [
                'getRequest' => $this->createConfiguredMock(
                    Http::class,
                    ['getFullActionName' => self::REQUEST_ACTION_NAME]
                ),
                'getEventName' => self::METADATA_EVENT_NAME
            ]
        );
    }
}
