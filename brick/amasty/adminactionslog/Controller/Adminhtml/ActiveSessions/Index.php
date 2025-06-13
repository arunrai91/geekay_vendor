<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActiveSessions;

use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActiveSessions;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractActiveSessions
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_AdminActionsLog::active_sessions');
        $resultPage->addBreadcrumb(__('Active Sessions'), __('Active Sessions'));
        $resultPage->getConfig()->getTitle()->prepend(__('Active Sessions'));

        return $resultPage;
    }
}
