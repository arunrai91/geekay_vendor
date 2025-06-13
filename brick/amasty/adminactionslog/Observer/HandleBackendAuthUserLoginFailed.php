<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Observer;

use Amasty\AdminActionsLog\Api\LoginAttemptManagerInterface;
use Amasty\AdminActionsLog\Model\OptionSource\LoginAttemptStatus;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class HandleBackendAuthUserLoginFailed implements ObserverInterface
{
    /**
     * @var LoginAttemptManagerInterface
     */
    private $loginAttemptManager;

    public function __construct(
        LoginAttemptManagerInterface $loginAttemptManager
    ) {
        $this->loginAttemptManager = $loginAttemptManager;
    }

    public function execute(Observer $observer)
    {
        $this->loginAttemptManager->saveAttempt($observer->getUserName(), LoginAttemptStatus::FAILED);
    }
}
