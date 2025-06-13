<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-08-09T15:25:32+00:00
 * File:          Block/Adminhtml/Destination/Edit/Tabs.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Destination\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('destination_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Export Destination'));
    }
}