<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\Notification;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Amasty\AdminActionsLog\Model\LoginAttempt\Notification\Type\AttemptNotificatorInterface;

class Processor
{
    /**
     * @var AttemptNotificatorInterface[]
     */
    private $notifierTypes;

    public function __construct(array $notifierTypes = [])
    {
        $this->notifierTypes = $notifierTypes;
    }

    public function execute(LoginAttemptInterface $loginAttempt)
    {
        $attemptStatus = $loginAttempt->getStatus();

        if ($attemptStatus !== null && isset($this->notifierTypes[$attemptStatus])) {
            $this->notifierTypes[$attemptStatus]->execute($loginAttempt);
        }
    }
}
