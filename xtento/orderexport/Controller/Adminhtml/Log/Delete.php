<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-12-08T18:56:39+00:00
 * File:          Controller/Adminhtml/Log/Delete.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Log;

class Delete extends \Xtento\OrderExport\Controller\Adminhtml\Log
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->logFactory->create();
        $model->load($id);

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This log entry does not exist anymore.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        try {
            $this->deleteFilesFromFilesystem($model);
            $model->delete();
            $this->messageManager->addSuccessMessage(__('Log entry has been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}