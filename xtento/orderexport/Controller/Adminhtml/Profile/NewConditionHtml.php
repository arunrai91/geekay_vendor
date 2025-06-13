<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-02-29T14:40:37+00:00
 * File:          Controller/Adminhtml/Profile/NewConditionHtml.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */
namespace Xtento\OrderExport\Controller\Adminhtml\Profile;

use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Xtento\OrderExport\Controller\Adminhtml\Profile
{
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        $profile = $this->profileFactory->create()->load($profileId);
        if (!$profile->getId()) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath('*/*/');
        }
        $this->registry->unregister('orderexport_profile');
        $this->registry->register('orderexport_profile', $profile);

        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create('Magento\CatalogRule\Model\Rule')
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultPage->setContents($html);
        return $resultPage;
    }
}
