<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\LoginAttempts;

use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractLoginAttempts;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractLoginAttempts
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_AdminActionsLog::login_attempts');
        $resultPage->addBreadcrumb(__('Login Attempts'), __('Login Attempts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Login Attempts'));

        return $resultPage;
    }
}
