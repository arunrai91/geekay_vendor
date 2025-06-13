<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Exception;

use Magento\Framework\Validation\ValidationException;

/**
 * Occurs when a user tries to create a new role with privileges exceeding the current ones.
 */
class AccessModeValidationException extends ValidationException
{
}
