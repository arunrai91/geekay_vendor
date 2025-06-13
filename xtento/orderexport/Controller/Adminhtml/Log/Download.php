<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-04-09T12:14:36+00:00
 * File:          Controller/Adminhtml/Log/Download.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Log;

use Magento\Framework\Exception\LocalizedException;

class Download extends \Xtento\OrderExport\Controller\Adminhtml\Log
{
    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * Download constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\OrderExport\Model\LogFactory $logFactory
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     */
    public function __construct(

        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\OrderExport\Model\LogFactory $logFactory,
        \Xtento\XtCore\Helper\Utils $utilsHelper
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $logFactory);
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $logId = intval($this->getRequest()->getParam('id', false));
        if (!$logId) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addWarningMessage(__('No log ID specified.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $exportedFiles = $this->getFilesForLogId($logId);
        if (!$exportedFiles) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $file = $this->utilsHelper->prepareFilesForDownload($exportedFiles);
        if (empty($file)) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addWarningMessage(
                __(
                    'No files have been exported or the backup files in the export_bkp folder have been deleted from the filesystem. Exported files don\'t exist anymore.'
                )
            );
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
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
    }
}
