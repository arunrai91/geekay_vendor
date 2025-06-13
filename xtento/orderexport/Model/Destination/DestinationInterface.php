<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-11-06T11:59:28+00:00
 * File:          Model/Destination/DestinationInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Destination;

interface DestinationInterface
{
    public function testConnection();
}