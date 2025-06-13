<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActiveSessions;

use Amasty\AdminActionsLog\Api\ActiveSessionManagerInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActiveSessions;
use Magento\Backend\App\Action\Context;

class Clear extends AbstractActiveSessions
{
    /**
     * @var ActiveSessionManagerInterface
     */
    private $activeSessionManager;

    public function __construct(
        Context $context,
        ActiveSessionManagerInterface $loginAttemptManager
    ) {
        parent::__construct($context);
        $this->activeSessionManager = $loginAttemptManager;
    }

    public function execute()
    {
        $this->activeSessionManager->terminateAll();

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
