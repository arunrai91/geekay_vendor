<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;

class HandlerResolver
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * @var array
     */
    private $matched = [];

    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $handlerName => $handlerData) {
            $handlerClass = $handlerData['handlerClass'] ?? null;
            $config = $handlerData['config'] ?? null;

            if (!is_subclass_of($handlerClass, LoggingActionInterface::class)) {
                throw new \LogicException(
                    sprintf('ActionType handler "%s" must implement %s', $handlerName, LoggingActionInterface::class)
                );
            }

            if (!$config instanceof TypeConfig) {
                throw new \LogicException(
                    sprintf('ActionType handler config "%s" must be instance of %s', $handlerName, TypeConfig::class)
                );
            }
        }

        usort($handlers, function ($first, $second) {
            return $first['config']->getPriority() <=> $second['config']->getPriority();
        });

        $this->handlers = $handlers;
    }

    public function getHandlers(string $actionName, string $event): array
    {
        $this->matchHandlers($actionName, $event);

        return array_map(function ($handlerData) {
            return $handlerData['class'];
        }, $this->matched[$actionName][$event]['handlers'] ?? []);
    }

    public function getValidators(string $actionName, string $event, string $handlerClass): array
    {
        $this->matchHandlers($actionName, $event);

        foreach ($this->matched[$actionName][$event]['handlers'] ?? [] as $handlerData) {
            if ($handlerData['class'] === $handlerClass) {
                return $handlerData['validators'];
            }
        }

        return [];
    }

    private function matchHandlers(string $actionName, string $event): void
    {
        if (isset($this->matched[$actionName][$event]['handlers'])) {
            return;
        }

        foreach ($this->handlers as $handlerData) {
            /** @var TypeConfig $config */
            $config = $handlerData['config'];

            if ($config->getEvent() === $event) {
                foreach ($config->getActions() as $handlerActionRegExp) {
                    if (preg_match("/$handlerActionRegExp/i", $actionName)) {
                        $this->matched[$actionName][$event]['handlers'][] = [
                            'class' => $handlerData['handlerClass'],
                            'validators' => $config->getValidators()
                        ];
                    }
                }
            }
        }
    }
}
