<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType;

use Amasty\AdminActionsLog\Logging\ActionType\Validation\ActionValidatorInterface;

class TypeConfig
{
    /**
     * @var array
     */
    private $actionsRegExp;

    /**
     * @var array
     */
    private $validators;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $event;

    public function __construct(
        array $actionsRegExp = [],
        array $validators = [],
        string $event = '',
        int $priority = 0
    ) {
        foreach ($validators as $validatorName => $validator) {
            if (!$validator instanceof ActionValidatorInterface) {
                throw new \LogicException(
                    sprintf(
                        'ActionType validator "%s" must implement %s',
                        $validatorName,
                        ActionValidatorInterface::class
                    )
                );
            }
        }

        $this->actionsRegExp = array_map(function ($action) {
            return (string)$action;
        }, $actionsRegExp);
        $this->validators = $validators;
        $this->event = $event;
        $this->priority = $priority;
    }

    public function getActions(): array
    {
        return $this->actionsRegExp;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return ActionValidatorInterface[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }
}
