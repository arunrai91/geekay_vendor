<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Dispatch;

use Amasty\AdminActionsLog\Api\ActiveSessionManagerInterface;
use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\LoginAttemptManagerInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryManagerInterface;
use Amasty\AdminActionsLog\Model\OptionSource\LoginAttemptStatus;

class AdminSession implements LoggingActionInterface
{
    public const LOGOUT_ACTION = 'logout';

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var ActiveSessionManagerInterface
     */
    private $activeSessionManager;

    /**
     * @var VisitHistoryManagerInterface
     */
    private $visitHistoryManager;

    /**
     * @var LoginAttemptManagerInterface
     */
    private $loginAttemptManager;

    public function __construct(
        MetadataInterface $metadata,
        ActiveSessionManagerInterface $activeSessionManager,
        VisitHistoryManagerInterface $visitHistoryManager,
        LoginAttemptManagerInterface $loginAttemptManager
    ) {
        $this->metadata = $metadata;
        $this->activeSessionManager = $activeSessionManager;
        $this->visitHistoryManager = $visitHistoryManager;
        $this->loginAttemptManager = $loginAttemptManager;
    }

    public function execute(): void
    {
        if ($this->metadata->getRequest()->getActionName() === self::LOGOUT_ACTION) {
            $this->activeSessionManager->terminate();
            $this->visitHistoryManager->endVisit();
            $this->loginAttemptManager->saveAttempt(null, LoginAttemptStatus::LOGOUT);
        } elseif (!$this->metadata->getRequest()->isAjax()) {
            $this->activeSessionManager->update();
        }
    }
}
