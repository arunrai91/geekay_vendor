<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Product\Sales;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::product_sales';

    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->updateTitles($resultPage);

        return $resultPage;
    }

    private function updateTitles(Page $page): void
    {
        $title = __('Related Product Sales')->render();
        $page->setActiveMenu('Amasty_Mostviewed::product_sales')
            ->addBreadcrumb($title, $title);
        $page->getConfig()->getTitle()->prepend($title);
    }
}
