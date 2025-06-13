<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Test\Unit\Logging\ActionType;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Logging\ActionType\Validation\ActionValidatorInterface;
use Amasty\AdminActionsLog\Logging\ActionType\ValidationComposite;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\AdminActionsLog\Logging\ActionType\ValidationComposite
 */
class ValidationCompositeTest extends TestCase
{
    /**
     * @var ValidationComposite
     */
    private $validationComposite;

    /**
     * @var LoggingActionInterface|MockObject
     */
    private $wrappedAction;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->wrappedAction = $this->getMockForAbstractClass(LoggingActionInterface::class);

        $this->validationComposite = $objectManager->getObject(
            ValidationComposite::class,
            [
                'wrappedAction' => $this->wrappedAction
            ]
        );
    }

    /**
     * @covers \Amasty\AdminActionsLog\Logging\ActionType\ValidationComposite::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute($validators, $executeCalls)
    {
        $validationCompositeReflection = new \ReflectionClass(ValidationComposite::class);

        $validatorsProp = $validationCompositeReflection->getProperty('validators');
        $validatorsProp->setAccessible(true);
        $validatorsProp->setValue($this->validationComposite, $validators);

        $this->wrappedAction->expects($this->exactly($executeCalls))->method('execute');
        $this->validationComposite->execute();
    }

    public function executeDataProvider(): array
    {
        $passedValidator = $this->getMockForAbstractClass(ActionValidatorInterface::class);
        $passedValidator->expects($this->once())->method('isValid')->willReturn(true);

        $failedValidator = $this->getMockForAbstractClass(ActionValidatorInterface::class);
        $failedValidator->expects($this->once())->method('isValid')->willReturn(false);

        return [
            'no validators' => [
                [],
                1
            ],
            'validation passed' => [
                [$passedValidator],
                1
            ],
            'validation failed' => [
                [$failedValidator],
                0
            ]
        ];
    }
}
