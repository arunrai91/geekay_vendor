<?php

/**
 * Product:       Xtento_XtCore
 * ID:            %!uniqueid!%
 * Last Modified: 2023-05-11T12:17:13+00:00
 * File:          Model/System/Config/Backend/License.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Backend;

class License extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $this->_registry->register('xtento_configuration_license_key', $this->getValue(), true);
    }
}
