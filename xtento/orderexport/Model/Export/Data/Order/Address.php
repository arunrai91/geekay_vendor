<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-01-22T16:29:19+00:00
 * File:          Model/Export/Data/Order/Address.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Order;

class Address extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * Directory country models
     *
     * @var \Magento\Directory\Model\Country[]
     */
    protected static $countryModels = [];

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Address constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->countryFactory = $countryFactory;
    }


    public function getConfiguration()
    {
        // Return config
        return [
            'name' => 'Billing/Shipping Address',
            'category' => 'Order',
            'description' => 'Export the billing/shipping address.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO, \Xtento\OrderExport\Model\Export::ENTITY_QUOTE, \Xtento\OrderExport\Model\Export::ENTITY_AWRMA, \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA, \Xtento\OrderExport\Model\Export::ENTITY_EERMA],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        // Billing Address
        if ($this->fieldLoadingRequired('billing')) {
            $this->writeArray = & $returnArray['billing']; // Write on billing level
            /** @var \Magento\Sales\Model\Order\Address $billingAddress */
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress && $billingAddress->getData()) {
                $billingAddress->explodeStreetAddress();
                foreach ($billingAddress->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                // Region Code
                if ($billingAddress->getRegionId() !== NULL && $this->fieldLoadingRequired('region_code')) {
                    $this->writeValue('region_code', $billingAddress->getRegionCode());
                }
                // Country - ISO3, Full Name
                if ($billingAddress->getCountryId() !== null && ($this->fieldLoadingRequired(
                            'country_name'
                        ) || $this->fieldLoadingRequired('country_iso3'))
                ) {
                    if (!isset(self::$countryModels[$billingAddress->getCountryId()])) {
                        $country = $this->countryFactory->create();
                        $country->load($billingAddress->getCountryId());
                        self::$countryModels[$billingAddress->getCountryId()] = $country;
                    }
                    if ($this->fieldLoadingRequired('country_name')) {
                        $this->writeValue('country_name', self::$countryModels[$billingAddress->getCountryId()]->getName());
                    }
                    if ($this->fieldLoadingRequired('country_iso3')) {
                        $this->writeValue(
                            'country_iso3',
                            self::$countryModels[$billingAddress->getCountryId()]->getData('iso3_code')
                        );
                    }
                }
                $this->addEECustomAddressAttributes($billingAddress);
            }
        }

        // Shipping Address
        if ($this->fieldLoadingRequired('shipping')) {
            $this->writeArray = & $returnArray['shipping']; // Write on shipping level
            /** @var \Magento\Sales\Model\Order\Address $shippingAddress */
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress && $shippingAddress->getData()) {
                $shippingAddress->explodeStreetAddress();
                foreach ($shippingAddress->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                // Region Code
                if ($shippingAddress->getRegionId() !== NULL && $this->fieldLoadingRequired('region_code')) {
                    $this->writeValue('region_code', $shippingAddress->getRegionCode());
                }
                // Country - ISO3, Full Name
                if ($shippingAddress->getCountryId() !== null && ($this->fieldLoadingRequired(
                            'country_name'
                        ) || $this->fieldLoadingRequired('country_iso3'))
                ) {
                    if (!isset(self::$countryModels[$shippingAddress->getCountryId()])) {
                        $country = $this->countryFactory->create();
                        $country->load($shippingAddress->getCountryId());
                        self::$countryModels[$shippingAddress->getCountryId()] = $country;
                    }
                    if ($this->fieldLoadingRequired('country_name')) {
                        $this->writeValue('country_name', self::$countryModels[$shippingAddress->getCountryId()]->getName());
                    }
                    if ($this->fieldLoadingRequired('country_iso3')) {
                        $this->writeValue(
                            'country_iso3',
                            self::$countryModels[$shippingAddress->getCountryId()]->getData('iso3_code')
                        );
                    }
                }
                $this->addEECustomAddressAttributes($shippingAddress);
                // Split street into street, housenumber, add.. needs to be fixed/reworked.
                /*$streetSplit = explode(" ", preg_replace("/[[:blank:]]+/u", " ", $shippingAddress->getStreet1()));
                if (count($streetSplit) > 0) {
                    $streetName = str_replace($streetSplit[count($streetSplit) - 1], '', $shippingAddress->getStreet1());
                    $streetLast = preg_replace('/[^A-Za-z0-9]/', '', $streetSplit[count($streetSplit) - 1]);
                    if (is_numeric($streetLast)) {
                        $streetAdd = '';
                        $streetNumber = $streetLast;
                    } else {
                        $streetAdd = $streetLast[count($streetLast)];
                        $streetNumber = intval($streetLast);
                    }
                    $this->writeValue('street_first', trim($streetName));
                    $this->writeValue('street_number', $streetNumber);
                    $this->writeValue('street_add', $streetAdd);
                }*/
            }
        }

        // Done
        return $returnArray;
    }

    protected function addEECustomAddressAttributes($address)
    {

    }
}