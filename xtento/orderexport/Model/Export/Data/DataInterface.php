<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-09-01T18:15:56+00:00
 * File:          Model/Export/Data/DataInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data;

interface DataInterface {
    public function getExportData($entityType, $collectionItem);
    public function getConfiguration();
}