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
use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession;
use Magento\Backend\App\Action\Context;

class Terminate extends AbstractActiveSessions
{
    /**
     * @var ActiveSessionManagerInterface
     */
    private $activeSessionManager;

    public function __construct(
        Context $context,
        ActiveSessionManagerInterface $activeSessionManager
    ) {
        parent::__construct($context);
        $this->activeSessionManager = $activeSessionManager;
    }

    public function execute()
    {
        if ($sessionId = $this->getRequest()->getParam(ActiveSession::SESSION_ID)) {
            $this->activeSessionManager->terminate($sessionId);
            $this->messageManager->addSuccessMessage('Session has been successfully terminated.');
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
