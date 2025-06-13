<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Cron;

use Amasty\AdminActionsLog\Api\ActiveSessionManagerInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryManagerInterface;

class UpdateActiveSessions
{
    /**
     * @var ActiveSessionManagerInterface
     */
    private $activeSessionManager;

    /**
     * @var VisitHistoryManagerInterface
     */
    private $visitHistoryManager;

    public function __construct(
        ActiveSessionManagerInterface $activeSessionManager,
        VisitHistoryManagerInterface $visitHistoryManager
    ) {
        $this->activeSessionManager = $activeSessionManager;
        $this->visitHistoryManager = $visitHistoryManager;
    }

    public function execute(): void
    {
        $inactiveSessions = $this->activeSessionManager->getInactiveSessions();

        foreach ($inactiveSessions as $sessionId) {
            $this->activeSessionManager->terminate($sessionId);
            $this->visitHistoryManager->endVisit($sessionId);
        }
    }
}
