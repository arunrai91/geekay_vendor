<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Validation\AccessMode;

use Amasty\Rolepermissions\Api\Data\RuleInterface;
use Amasty\Rolepermissions\Exception\AccessModeValidationException;

interface ValidatorInterface
{
    /**
     * @throws AccessModeValidationException
     */
    public function validate(array $data, RuleInterface $rule): void;
}
