<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Validation\AccessMode;

use Amasty\Rolepermissions\Api\Data\RuleInterface;

class ValidatorComposite implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    public function validate(array $data, RuleInterface $rule): void
    {
        foreach ($this->validators as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $validator->validate($data, $rule);
            }
        }
    }
}
