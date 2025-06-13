<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-02-29T14:40:37+00:00
 * File:          Controller/Adminhtml/Profile/Fields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Profile;

class Fields extends \Xtento\OrderExport\Controller\Adminhtml\Profile
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('profile_id');
        $model = $this->profileFactory->create()->load($id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath('*/*/');
        }
        $this->registry->unregister('orderexport_profile');
        $this->registry->register('orderexport_profile', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
