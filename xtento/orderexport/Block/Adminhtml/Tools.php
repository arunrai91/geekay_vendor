<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-09-10T15:24:10+00:00
 * File:          Block/Adminhtml/Tools.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml;

class Tools extends \Magento\Backend\Block\Template
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Xtento_OrderExport::tools.phtml');
    }
}
