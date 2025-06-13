<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\SuspiciousDetection;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

class Detector
{
    /**
     * @var Type\DetectorInterface[]
     */
    private $detectionTypes;

    public function __construct(array $detectionTypes = [])
    {
        $this->detectionTypes = $detectionTypes;
    }

    public function isSuspicious(string $type, LoginAttemptInterface $loginAttempt): bool
    {
        return isset($this->detectionTypes[$type])
            ? $this->detectionTypes[$type]->isSuspiciousAttempt($loginAttempt)
            : false;
    }
}
