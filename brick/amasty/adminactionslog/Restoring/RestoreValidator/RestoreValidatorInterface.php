<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Restoring\RestoreValidator;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;

interface RestoreValidatorInterface
{
    public function isValid(LogEntryInterface $logEntry): bool;
}
