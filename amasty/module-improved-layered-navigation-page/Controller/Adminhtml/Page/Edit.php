<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Amasty\ShopbyPage\Model\Request\Page\Registry as PageRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\DataObjectHelper;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var PageRepositoryInterface
     */
    private PageRepositoryInterface $pageRepository;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var PageRegistry
     */
    private PageRegistry $pageRegistry;

    public function __construct(
        PageFactory $resultPageFactory,
        PageInterfaceFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        DataObjectHelper $dataObjectHelper,
        PageRegistry $pageRegistry,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $pageFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->request = $context->getRequest();
        $this->pageRegistry = $pageRegistry;

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
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('page')
            ->addBreadcrumb(__('Manage Custom Pages'), __('Manage Custom Pages'));
        return $resultPage;
    }

    /**
     * Edit page
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->request->getParam('id');
        $isExisting = (bool)$id;

        $page = $this->pageFactory->create();
        if ($isExisting
            && !($page = $this->loadPage($id))
        ) {
            $result = $this->resultRedirectFactory->create();
            $result->setPath('amasty_shopbypage/*/index');
        } else {
            $data = $this->_session->getFormData(true);

            if (!empty($data)) {
                $this->dataObjectHelper->populateWithArray(
                    $page,
                    $data,
                    \Amasty\ShopbyPage\Api\Data\PageInterface::class
                );
            }
            $this->pageRegistry->set($page);

            /** @var \Magento\Backend\Model\View\Result\Page $result */
            $result = $this->initAction();
            $result->addBreadcrumb(
                $id ? __('Edit Improved Navigation Page') : __('New Improved Navigation Page'),
                $id ? __('Edit Improved Navigation Page') : __('New Improved Navigation Page')
            );
            $result->getConfig()->getTitle()->prepend(__('Improved Navigation Pages'));

            if ($isExisting) {
                $result->getConfig()->getTitle()->prepend($page->getTitle());
            } else {
                $result->getConfig()->getTitle()->prepend(__('New Improved Navigation Page'));
            }
        }

        return $result;
    }

    /**
     * @param $pageId
     *
     * @return \Amasty\ShopbyPage\Api\Data\PageInterface|bool
     */
    private function loadPage($pageId)
    {
        try {
            $page = $this->pageRepository->get($pageId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the page.'));
            $page = false;
        }

        return $page;
    }
}
