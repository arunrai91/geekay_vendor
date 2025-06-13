<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Occurs when the user tries to get an entity to which they don't have access.
 */
class AccessDeniedException extends LocalizedException
{
}
