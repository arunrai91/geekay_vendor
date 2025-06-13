<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2015-08-09T14:37:03+00:00
 * File:          Model/System/Config/Source/Destination/Type.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Destination;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Type implements ArrayInterface
{
    /**
     * @var \Xtento\OrderExport\Model\Destination
     */
    protected $destinationModel;

    /**
     * @param \Xtento\OrderExport\Model\Destination $destinationModel
     */
    public function __construct(\Xtento\OrderExport\Model\Destination $destinationModel)
    {
        $this->destinationModel = $destinationModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->destinationModel->getTypes();
    }

    public function getName($type) {
        foreach ($this->toOptionArray() as $optionType => $name) {
            if ($optionType == $type) {
                return $name;
            }
        }
        return '';
    }
}
