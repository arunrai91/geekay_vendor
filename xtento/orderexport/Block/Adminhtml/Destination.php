<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-09-10T15:24:06+00:00
 * File:          Block/Adminhtml/Destination.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml;

class Destination extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_addButtonLabel = __('Add New Destination');
        parent::_construct();
    }
}
