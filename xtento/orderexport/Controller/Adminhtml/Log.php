<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-08-27T13:37:17+00:00
 * File:          Controller/Adminhtml/Log.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml;

abstract class Log extends \Xtento\OrderExport\Controller\Adminhtml\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Xtento\OrderExport\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @var \Xtento\OrderExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * Log constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\OrderExport\Model\LogFactory $logFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\OrderExport\Model\LogFactory $logFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig);
        $this->registry = $registry;
        $this->escaper = $escaper;
        $this->logFactory = $logFactory;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Xtento_OrderExport::log');
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_OrderExport::log');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Execution Log'), __('Execution Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Export - Execution Log'));
    }

    protected function getFilesForLogId($logId, $massDownload = false)
    {
        $model = $this->logFactory->create();
        $model->load($logId);

        if (!$model->getId()) {
            if (!$massDownload) {
                $this->messageManager->addErrorMessage(__('This log entry (ID: %1) does not exist anymore.', $logId));
            }
            return false;
        }

        $filesNotFound = 0;
        $exportedFiles = [];
        $savedFiles = $model->getFiles();
        if (empty($savedFiles)) {
            if (!$massDownload) {
                $this->messageManager->addWarningMessage(
                    __('There is nothing to download. No files have been saved with this export, or you disabled "Save local copies of exports" in the profile. (Log ID: %1)', $logId)
                );
            }
            return false;
        }
        $savedFiles = explode("|", $savedFiles);

        $baseFilenames = [];
        foreach ($savedFiles as $filePath) {
            array_push($baseFilenames, basename($filePath));
        }
        $baseFilenames = array_unique($baseFilenames);

        foreach ($baseFilenames as $filename) {
            $filePath = $this->moduleHelper->getExportBkpDir() . $logId . '_' . $filename;
            $data = false;
            if (file_exists($filePath)) {
                try {
                    $data = file_get_contents($filePath);
                } catch (\Exception $e) {}
            }
            if ($data === false && !$this->getRequest()->getParam('force', false)) {
                $filesNotFound++;
                if (!$massDownload) {
                    $this->messageManager->addWarningMessage(
                        __('File not found in local backup directory: %1 (Log ID: %2)', $filePath, $logId)
                    );
                }
                if ($filesNotFound == count($baseFilenames)) {
                    return false;
                }
            }
            $exportedFiles[$filename] = $data;
        }
        if ($filesNotFound > 0 && $filesNotFound !== count($baseFilenames) && !$this->getRequest()->getParam(
                'force',
                false
            )
        ) {
            $this->messageManager->addComplexWarningMessage(
                'backendHtmlMessage',
                [
                    'html' => (string)__(
                        'One or more files of this export have been deleted from the local backup directory. Please click <a href="%1">here</a> to download the remaining existing files. (Log ID: %2)',
                        $this->getUrl('*/*/*', ['id' => $logId, 'force' => true]),
                        $logId
                    )
                ]
            );
            return false;
        }

        return $exportedFiles;
    }

    protected function deleteFilesFromFilesystem($model)
    {
        $savedFiles = $model->getFiles();
        if (empty($savedFiles)) {
            return false;
        }
        $savedFiles = explode("|", $savedFiles);

        $baseFilenames = [];
        foreach ($savedFiles as $filePath) {
            array_push($baseFilenames, basename($filePath));
        }
        $baseFilenames = array_unique($baseFilenames);

        foreach ($baseFilenames as $filename) {
            $filePath = $this->moduleHelper->getExportBkpDir() . $model->getId() . '_' . $filename;
            try {
                unlink($filePath);
            } catch (\Exception $e) {
                $this->messageManager->addWarningMessage(
                    __('File could not be deleted, probably a permissions issue or file has been deleted already: %1', $filePath)
                );
            }
        }
        return true;
    }
}
