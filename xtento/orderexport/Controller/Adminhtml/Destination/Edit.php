<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:20:37+00:00
 * File:          Controller/Adminhtml/Destination/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Destination;

class Edit extends \Xtento\OrderExport\Controller\Adminhtml\Destination
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $healthCheck = $this->healthCheck();
        if ($healthCheck !== true) {
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath($healthCheck);
        }

        $id = $this->getRequest()->getParam('id');
        $model = $this->destinationFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This destination no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                return $resultRedirect->setPath('*/*/');
            }
            if ($model->getType() == \Xtento\OrderExport\Model\Destination::TYPE_LOCAL) {
                if (!$model->getPath()) {
                    $model->setPath('./var/export/');
                }
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->registry->unregister('orderexport_destination');
        $this->registry->register('orderexport_destination', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $this->updateMenu($resultPage);

        if ($this->registry->registry('orderexport_destination') && $this->registry->registry(
            'orderexport_destination'
        )->getId()
        ) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Destination'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Destination'));
        }

        return $resultPage;
    }
}