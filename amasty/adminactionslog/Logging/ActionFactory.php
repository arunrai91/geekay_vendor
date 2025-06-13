<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\ActionType;
use Magento\Framework\ObjectManagerInterface;

class ActionFactory
{
    /**
     * Handler fetch strategies.
     */
    public const FETCH_ONE = 1;
    public const FETCH_ANY = 2;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ActionType\CompositeFactory
     */
    private $compositeFactory;

    /**
     * @var ActionType\HandlerResolver
     */
    private $actionHandlerResolver;

    /**
     * @var ActionType\ValidationCompositeFactory
     */
    private $validationCompositeFactory;

    /**
     * @var string
     */
    private $dummyHandlerClass;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ActionType\CompositeFactory $compositeFactory,
        ActionType\HandlerResolver $actionHandlerResolver,
        ActionType\ValidationCompositeFactory $validationCompositeFactory,
        string $dummyHandlerClass = ActionType\Dummy::class
    ) {
        $this->objectManager = $objectManager;
        $this->compositeFactory = $compositeFactory;
        $this->actionHandlerResolver = $actionHandlerResolver;
        $this->validationCompositeFactory = $validationCompositeFactory;
        $this->dummyHandlerClass = $dummyHandlerClass;
    }

    public function create(MetadataInterface $metadata, int $fetchStrategy = self::FETCH_ONE): LoggingActionInterface
    {
        $loggingActions = [];
        $actionName = $metadata->getRequest()->getFullActionName();
        $handlerClasses = $this->actionHandlerResolver->getHandlers($actionName, $metadata->getEventName());

        switch ($fetchStrategy) {
            case self::FETCH_ONE:
                $handlerClasses = [$handlerClasses[0] ?? $this->dummyHandlerClass];
                break;
            default:
                $handlerClasses = !empty($handlerClasses) ? $handlerClasses : [$this->dummyHandlerClass];
        }

        foreach ($handlerClasses as $handlerClass) {
            $loggingActions[] = $this->initializeLoggingAction($handlerClass, $metadata);
        }

        return $this->compositeFactory->create(['actions' => $loggingActions]);
    }

    private function initializeLoggingAction(string $class, MetadataInterface $metadata): LoggingActionInterface
    {
        $action = $this->objectManager->create($class, ['metadata' => $metadata]);
        $eventName = $metadata->getEventName();
        $actionName = $metadata->getRequest()->getFullActionName();
        $validators = $this->actionHandlerResolver->getValidators($actionName, $eventName, $class);

        if ($validators) {
            return $this->validationCompositeFactory->create([
                'metadata' => $metadata,
                'wrappedAction' => $action,
                'validators' => $validators
            ]);
        }

        return $action;
    }
}
