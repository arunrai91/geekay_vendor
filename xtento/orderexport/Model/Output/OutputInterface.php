<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-08-17T13:41:32+00:00
 * File:          Model/Output/OutputInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Output;

interface OutputInterface
{
    public function convertData($exportArray);
}