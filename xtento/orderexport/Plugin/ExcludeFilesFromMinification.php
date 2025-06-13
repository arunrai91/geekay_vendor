<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2017-11-13T17:32:12+00:00
 * File:          Plugin/ExcludeFilesFromMinification.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Plugin;

use Magento\Framework\View\Asset\Minification;

class ExcludeFilesFromMinification
{
    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }
        $result[] = 'Xtento_OrderExport/js/ace/mode-xml';
        $result[] = 'Xtento_OrderExport/js/ace/theme-eclipse';
        return $result;
    }
}