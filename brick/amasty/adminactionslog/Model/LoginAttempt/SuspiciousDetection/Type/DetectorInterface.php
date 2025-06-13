<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

interface DetectorInterface
{
    public function isSuspiciousAttempt(LoginAttemptInterface $loginAttempt): bool;
}
