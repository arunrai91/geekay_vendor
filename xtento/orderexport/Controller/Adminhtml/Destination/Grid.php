<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-09-09T17:58:19+00:00
 * File:          Controller/Adminhtml/Destination/Grid.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Destination;

class Grid extends \Xtento\OrderExport\Controller\Adminhtml\Destination
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
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

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT);
        return $resultPage;
    }
}
