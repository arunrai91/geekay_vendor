<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Amasty\ShopbyPage\Model\Page;
use Amasty\ShopbyPage\Model\Page\ImagesManager;
use Magento\Backend\App\Action;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;

class Save extends Action
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
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private ExtensibleDataObjectConverter $extensibleDataObjectConverter;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ImagesManager
     */
    private ImagesManager $imagesManager;

    /**
     * @var Session
     */
    private Session $session;

    public function __construct(
        Context $context,
        PageInterfaceFactory $pageDataFactory,
        PageRepositoryInterface $pageRepository,
        DataObjectHelper $dataObjectHelper,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ImagesManager $imagesManager
    ) {
        $this->pageDataFactory = $pageDataFactory;
        $this->pageRepository = $pageRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->imagesManager = $imagesManager;
        $this->session = $context->getSession();
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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->request->getPostValue();
        $id = $this->request->getParam('page_id', false);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                /** @var Page $pageData */
                $pageData = $this->pageDataFactory->create();

                if ($id) {
                    $flatArray = $this->extensibleDataObjectConverter->toNestedArray(
                        $this->pageRepository->get($id),
                        [],
                        \Amasty\ShopbyPage\Api\Data\PageInterface::class
                    );

                    if (!isset($data['conditions'])) {
                        unset($flatArray['conditions']);
                    }
                    $data = array_merge($flatArray, $data);
                }

                $this->dataObjectHelper->populateWithArray(
                    $pageData,
                    $data,
                    \Amasty\ShopbyPage\Api\Data\PageInterface::class
                );

                $this->validateConditions($data);

                if (isset($data['image_delete']) && $pageData->getImage()) {
                    $this->imagesManager->removeImage($pageData->getImage());
                    $pageData->setImage(null);
                }

                try {
                    $imageName = $this->imagesManager->uploadImage('image');
                    if ($pageData->getImage()) {
                        $this->imagesManager->removeImage($pageData->getImage());
                    }
                    $pageData->setImage($imageName);
                } catch (\Exception $e) {
                    if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                        $this->messageManager->addErrorMessage(__('Image file was not uploaded'));
                    }
                }

                $pageData = $this->pageRepository->save($pageData);

                $this->messageManager->addSuccessMessage(__('You saved this page.'));
                $this->_session->setFormData(false);
                if ($this->request->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $pageData->getPageId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }

            $this->session->setFormData($data);

            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } else {
                return $resultRedirect->setPath('*/*/new');
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    private function validateConditions(array $data)
    {
        if (!isset($data['conditions'])) {
            throw new LocalizedException(__('Please select the Filter Conditions'));
        }
    }
}
