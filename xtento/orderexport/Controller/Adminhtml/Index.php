<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-04-28T15:48:36+00:00
 * File:          Controller/Adminhtml/Index.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml;

abstract class Index extends \Xtento\OrderExport\Controller\Adminhtml\Action
{
    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('Xtento_OrderExport::profiles');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Sales Export'), __('Sales Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sales Export'));
    }
}
