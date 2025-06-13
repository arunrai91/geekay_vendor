<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-12-02T15:04:42+00:00
 * File:          Controller/Adminhtml/Manual/GridPost.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Manual;

use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

class GridPost extends \Xtento\OrderExport\Controller\Adminhtml\Manual
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirectInterface;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * GridPost constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\OrderExport\Model\ExportFactory $exportFactory
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Magento\Framework\Registry $registry,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Xtento\OrderExport\Helper\Entity $entityHelper
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig, $profileFactory);
        $this->registry = $registry;
        $this->exportFactory = $exportFactory;
        $this->utilsHelper = $utilsHelper;
        $this->entityHelper = $entityHelper;
        $this->redirectInterface = $context->getRedirect();
        $this->filter = $filter;
    }

    /*
     * Export from grid handler
     */
    public function execute()
    {
        $exportType = $this->getRequest()->getParam('type', false);
        if (!$exportType) {
            $this->messageManager->addErrorMessage(__('Export type not specified.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
             $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
             $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
             return $resultRedirect;
        }
        $exportEntity = $this->entityHelper->getExportEntity($exportType);
        $exportEntityClass = $this->_objectManager->create($exportEntity);
        $collection = $this->filter->getCollection($exportEntityClass->getCollection());
        $exportIds = $collection->getAllIds();
        if (!$exportIds) {
            $this->messageManager->addErrorMessage(__('Please select objects to export.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
             $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
             $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
             return $resultRedirect;
        }
        $profileId = $this->getRequest()->getParam('profile_id', false);
        if (!$profileId) {
            $this->messageManager->addErrorMessage(__('No export profile specified.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
             $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
             $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
             return $resultRedirect;
        }
        $profile = $this->profileFactory->create()->load($profileId);
        // Export
        try {
            $beginTime = time();
            $exportedFiles = $this->exportFactory->create()->setProfile($profile)->gridExport($exportIds);
            $endTime = time();
            if ($profile->getStartDownloadManualExport()) {
                /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $file = $this->utilsHelper->prepareFilesForDownload($exportedFiles);
                if (empty($file)) {
                    throw new LocalizedException(
                        __('No files have been exported. Please check your XSL Template and/or profile filters.')
                    );
                }
                $resultPage->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Content-type', 'application/octet-stream', true)
                    ->setHeader('Content-Length', strlen($file['data']))
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"')
                    ->setHeader('Last-Modified', date('r'));
                $resultPage->setContents($file['data']);
                return $resultPage;
            } else {
                $this->messageManager->addComplexSuccessMessage(
                    'backendHtmlMessage',
                    [
                        'html' => (string)__(
                            'Export of %1 %2s completed successfully in %3 seconds. Click <a href="%4">here</a> to download exported files.',
                            $this->registry->registry('orderexport_log')->getRecordsExported(),
                            $profile->getEntity(),
                            ($endTime - $beginTime),
                            $this->getUrl(
                                'xtento_orderexport/log/download',
                                ['id' => $this->registry->registry('orderexport_log')->getId()]
                            )
                        )
                    ]
                );
                if ($this->registry->registry('orderexport_log')->getResult() !== \Xtento\OrderExport\Model\Log::RESULT_SUCCESSFUL) {
                    $this->messageManager->addErrorMessage(
                        __(nl2br($this->registry->registry('orderexport_log')->getResultMessage()))
                    );
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: %1', nl2br($e->getMessage())));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
         $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
         $resultRedirect->setPath($this->redirectInterface->getRefererUrl());
         return $resultRedirect;
    }
}
