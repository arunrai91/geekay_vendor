<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-10-11T13:28:37+00:00
 * File:          Model/History.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model;

/**
 * Class History
 * @package Xtento\OrderExport\Model
 */
class History extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Xtento\OrderExport\Model\ResourceModel\History');
        $this->_collectionName = 'Xtento\OrderExport\Model\ResourceModel\History\Collection';
    }
}