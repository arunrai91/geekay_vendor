<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring;

use Amasty\AdminActionsLog\Restoring\RestoreValidator\RestoreValidatorInterface;

class RestoreValidatorProvider
{
    /**
     * @var RestoreValidatorInterface[]
     */
    private $validators;

    /**
     * @param array $validatorTypes ['validatorName' => ValidatorClass ]
     */
    public function __construct(
        array $validatorTypes = []
    ) {
        $this->initializeValidatorTypes($validatorTypes);
    }

    public function getValidators(): array
    {
        return $this->validators;
    }

    private function initializeValidatorTypes(array $validatorTypes): void
    {
        foreach ($validatorTypes as $type => $validator) {
            if (!$validator instanceof RestoreValidatorInterface) {
                throw new \LogicException(
                    sprintf('Validator type must implement %s', RestoreValidatorInterface::class)
                );
            }
            $this->validators[$type] = $validator;
        }
    }
}
