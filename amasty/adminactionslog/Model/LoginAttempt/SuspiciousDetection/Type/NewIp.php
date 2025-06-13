<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

class NewIp extends AbstractDetection
{
    public function isSuspiciousAttempt(LoginAttemptInterface $loginAttempt): bool
    {
        $lastAttempt = $this->getLastSucceedAttempt($loginAttempt);

        if ($lastAttempt->getId() && !empty($lastAttempt->getIp())) {
            return $loginAttempt->getIp() !== $lastAttempt->getIp();
        }

        return false;
    }
}
