<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActionsLog;

use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActionsLog;
use Magento\Framework\Controller\ResultFactory;

class Edit extends AbstractActionsLog
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_AdminActionsLog::amaudit');
        $resultPage->addBreadcrumb(__('Admin Actions Log'), __('Actions Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Actions Log'));

        return $resultPage;
    }
}
