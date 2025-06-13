<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-09-29T14:37:38+00:00
 * File:          Model/Export/Condition/ObjectCondition.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Condition;

class ObjectCondition extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * @var CustomFactory
     */
    protected $conditionCustomFactory;

    /**
     * ObjectCondition constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param CustomFactory $conditionCustomFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        \Xtento\OrderExport\Model\Export\Condition\CustomFactory $conditionCustomFactory,
        array $data = []
    ) {
        $this->conditionCustomFactory = $conditionCustomFactory;
        parent::__construct(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
    }

    public function loadAttributeOptions()
    {
        $attributes = [];

        $conditionCustom = $this->conditionCustomFactory->create();

        $attributes = array_merge(
            $attributes,
            $conditionCustom->getCustomAttributes()
        );
        $attributes = array_merge(
            $attributes,
            $conditionCustom->getCustomNotMappedAttributes()
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'total_qty':
                return 'numeric';

            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        // Get type for custom
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = $this->_shippingAllmethods->toOptionArray();
                    break;

                case 'payment_method':
                    $options = $this->_paymentAllmethods->toOptionArray();
                    array_unshift($options, ['value' => '', 'label' => __('Empty (no value set)')]);
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->getAttribute() == 'payment_method' && !$object->hasPaymentMethod()) {
            if ($object->getOrder() && $object->getOrder()->getPayment()) {
                $object->setPaymentMethod($object->getOrder()->getPayment()->getMethod());
            } else if ($object->getPayment()) {
                $object->setPaymentMethod($object->getPayment()->getMethod());
            }
        }

        if ($object instanceof \Magento\Sales\Model\Order\Shipment) {
            $object = $object->getOrder();
        }

        #Zend_Debug::dump($object->getData());
        #Zend_Debug::dump($this->validateAttribute($object->getData($this->getAttribute())), $object->getData($this->getAttribute()));

        return $this->validateAttribute($object->getData($this->getAttribute()));
    }
}
