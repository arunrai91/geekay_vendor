<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-01-08T14:28:14+00:00
 * File:          Block/Adminhtml/Profile/Edit/DefaultTemplatePopup.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile\Edit;

use Magento\Backend\Block\Template;

class DefaultTemplatePopup extends Template
{
    public function isDemoEnvironment()
    {
        return false;
    }
}
