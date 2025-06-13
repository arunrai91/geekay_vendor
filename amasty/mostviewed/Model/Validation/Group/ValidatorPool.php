<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Validation\Group;

use Amasty\Mostviewed\Model\Validation\ValidatorInterface;
use Amasty\Mostviewed\Model\Validation\ValidatorPoolInterface;

class ValidatorPool implements ValidatorPoolInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private array $validators;

    public function __construct(
        ?array $validators = []
    ) {
        $this->validators = [];
        $this->initValidators($validators);
    }

    public function getValidators(): array
    {
        return $this->validators;
    }

    private function initValidators(array $validators): void
    {
        foreach ($validators as $validator) {
            if (!($validator instanceof ValidatorInterface)) {
                throw new \RuntimeException(
                    __(
                        'Group Validator %1 must implement %2 interface',
                        get_class($validator),
                        ValidatorInterface::class
                    )->render()
                );
            }

            $this->validators[] = $validator;
        }
    }
}
