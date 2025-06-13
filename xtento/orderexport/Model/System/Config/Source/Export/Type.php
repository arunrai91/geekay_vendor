<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-01T16:14:33+00:00
 * File:          Model/System/Config/Source/Export/Type.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Export;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Type implements ArrayInterface
{
    /**
     * @var \Xtento\OrderExport\Model\Export
     */
    protected $exportModel;

    /**
     * Type constructor.
     * @param \Xtento\OrderExport\Model\Export $exportModel
     */
    public function __construct(\Xtento\OrderExport\Model\Export $exportModel)
    {
        $this->exportModel = $exportModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->exportModel->getExportTypes();
    }
}
