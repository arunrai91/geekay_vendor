<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-10-20T12:16:27+00:00
 * File:          Model/Export/Condition/Address/Shipping.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */
namespace Xtento\OrderExport\Model\Export\Condition\Address;

use Xtento\OrderExport\Model\Export\Condition\ObjectCondition;

class Shipping extends ObjectCondition
{
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $address = $object;
        if (!$address instanceof \Magento\Sales\Model\Order\Address) {
            $address = $object->getShippingAddress();
            if (!$address) {
                return false;
            }
        }

        return $this->validateAttribute($address->getData($this->getAttribute()));
    }
}
