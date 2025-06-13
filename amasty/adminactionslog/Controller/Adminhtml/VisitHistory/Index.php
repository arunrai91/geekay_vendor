<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\VisitHistory;

use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractVisitHistory;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractVisitHistory
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_AdminActionsLog::page_visit_history');
        $resultPage->addBreadcrumb(__('Visit History'), __('Visit History'));
        $resultPage->getConfig()->getTitle()->prepend(__('Visit History'));

        return $resultPage;
    }
}
