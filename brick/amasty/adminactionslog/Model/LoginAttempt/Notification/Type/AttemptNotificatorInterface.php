<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt\Notification\Type;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;

interface AttemptNotificatorInterface
{
    /**
     * Performs Login Attempt notification.
     *
     * @param LoginAttemptInterface $loginAttempt
     */
    public function execute(LoginAttemptInterface $loginAttempt): void;
}
