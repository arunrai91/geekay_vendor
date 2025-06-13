<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use \Magento\Backend\App\Action;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class Delete extends Action
{
    /**
     * @var PageInterfaceFactory
     */
    private PageInterfaceFactory $pageDataFactory;

    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    public function __construct(
        Action\Context $context,
        PageInterfaceFactory $pageDataFactory,
        PageRepositoryInterface $pageRepository
    ) {
        $this->pageDataFactory = $pageDataFactory;
        $this->pageRepository = $pageRepository;
        $this->request = $context->getRequest();
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyPage::page');
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->request->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->pageRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The page has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a page to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
