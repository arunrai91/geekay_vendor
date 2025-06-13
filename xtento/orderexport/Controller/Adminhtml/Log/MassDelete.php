<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-09-09T20:08:19+00:00
 * File:          Controller/Adminhtml/Log/MassDelete.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Log;

class MassDelete extends \Xtento\OrderExport\Controller\Adminhtml\Log
{
    /**
     * Mass delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $ids = $this->getRequest()->getParam('log');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select log entries to delete.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        try {
            foreach ($ids as $id) {
                $model = $this->logFactory->create();
                $model->load($id);
                $this->deleteFilesFromFilesystem($model);
                $model->delete();
            }
            $this->messageManager->addSuccessMessage(
                __('Total of %1 log entries were successfully deleted.', count($ids))
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}