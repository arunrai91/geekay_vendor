<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-08-05T12:18:24+00:00
 * File:          Model/Export/Data/Shared/Customer.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Shared;

class Customer extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * Directory country models
     *
     * @var \Magento\Directory\Model\Country[]
     */
    protected static $countryModels = [];

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Customer constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
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
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->customerFactory = $customerFactory;
        $this->groupFactory = $groupFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->countryFactory = $countryFactory;
    }

    public function getConfiguration()
    {
        // Init cache
        if (!isset($this->cache['customer_group'])) {
            $this->cache['customer_group'] = [];
        }
        // Return config
        return [
            'name' => 'Customer information',
            'category' => 'Customer',
            'description' => 'Export customer information from customer tables.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO, \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER],
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        // Fetch fields to export
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
            if ($this->_registry->registry('orderexport_log') && $this->_registry->registry('orderexport_log')->getExportType() == \Xtento\OrderExport\Model\Export::EXPORT_TYPE_EVENT) {
                $customer = $collectionItem->getObject();
            } else {
                $customer = $this->customerFactory->create()->load($collectionItem->getObject()->getId());
            }
            $this->writeArray = & $returnArray; // Write on main level
            // Is subscribed to newsletter
            if ($this->fieldLoadingRequired('is_subscribed')) {
                $subscription = $this->subscriberFactory->create()->loadByEmail($customer->getEmail());
                if ($subscription->getId()) {
                    $this->writeValue('is_subscribed', $subscription->isSubscribed());
                } else {
                    $this->writeValue('is_subscribed', '0');
                }
            }
            // Extended newsletter_subscriber information
            if ($this->fieldLoadingRequired('newsletter_subscriber')) {
                if (!isset($subscription)) {
                    $subscription = $this->subscriberFactory->create()->loadByEmail($customer->getEmail());
                }
                if ($subscription->getId()) {
                    $returnArray['newsletter_subscriber'] = [];
                    $this->writeArray = & $returnArray['newsletter_subscriber'];
                    foreach ($subscription->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                    $this->writeArray = & $returnArray;
                }
            }
        } else {
            $this->writeArray = & $returnArray['customer']; // Write on customer level
            $order = $collectionItem->getOrder();
            // Is subscribed to newsletter
            if ($this->fieldLoadingRequired('is_subscribed')) {
                $subscription = $this->subscriberFactory->create()->loadByEmail($order->getCustomerEmail());
                if ($subscription->getId()) {
                    $this->writeValue('is_subscribed', $subscription->isSubscribed());
                } else {
                    $this->writeValue('is_subscribed', '0');
                }
            }
            // Extended newsletter_subscriber information
            if ($this->fieldLoadingRequired('newsletter_subscriber')) {
                if (!isset($subscription)) {
                    $subscription = $this->subscriberFactory->create()->loadByEmail($order->getCustomerEmail());
                }
                if ($subscription->getId()) {
                    $returnArray['customer']['newsletter_subscriber'] = [];
                    $this->writeArray = & $returnArray['customer']['newsletter_subscriber'];
                    foreach ($subscription->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                    $this->writeArray = & $returnArray['customer'];
                }
            }
            // Load customer
            $customer = $this->customerFactory->create()->load($order->getCustomerId());
            if (!$customer || !$customer->getId()) {
                if ($this->getShowEmptyFields()) { // If this is debug mode and no customer was found, still output the customer attribute codes
                    $collection = $this->customerCollectionFactory->create()
                        ->addAttributeToSelect('*');
                    $collection->getSelect()->limit(1, 0); // At least one customer must exist for this to work
                    if ($customer = $collection->getFirstItem()) {
                        foreach ($customer->getData() as $key => $value) {
                            if ($key == 'entity_id') {
                                continue;
                            }
                            $this->writeValue($key, NULL);
                        }
                    }
                }
                return $returnArray;
            }
        }

        if ($entityType !== \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER && !$this->fieldLoadingRequired('customer')) {
            return $returnArray;
        }

        // Customer data
        foreach ($customer->getData() as $key => $value) {
            if ($key == 'entity_id') {
                continue;
            }
            /*$attribute = $customer->getResource()->getAttribute($key);
            if ($attribute instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute) {
                $attribute->setStoreId($order->getStoreId());
            }

            $attrText = '';
            if ($attribute) {
                if ($attribute->usesSource()) {
                    try {
                        $attrText = $product->getAttributeText($key);
                    } catch (\Exception $e) {
                        //echo "Problem with attribute $key: ".$e->getMessage();
                        continue;
                    }
                }
            }
            if (!empty($attrText)) {
                if (is_array($attrText)) {
                    // Multiselect:
                    foreach ($attrText as $index => $val) {
                        if (!is_array($index) && !is_array($val)) {
                            $this->writeValue($key . '_value_' . $index, $val);
                        }
                    }
                    $this->writeValue($key, implode(",", $attrText));
                } else {
                    if ($attribute->getFrontendInput() == 'multiselect') {
                        $this->writeValue($key . '_value_0', $attrText);
                    }
                    $this->writeValue($key, $attrText);
                }
            } else {
                $this->writeValue($key, $value);
            }*/
            $this->writeValue($key, $value);
        }

        // Customer group
        if ($this->fieldLoadingRequired('customer_group')) {
            if (isset($this->cache['customer_group'][$customer->getGroupId()])) {
                $this->writeValue('customer_group', $this->cache['customer_group'][$customer->getGroupId()]);
            } else {
                $customerGroup = $this->groupFactory->create()->load($customer->getGroupId());
                if ($customerGroup && $customerGroup->getId()) {
                    $this->writeValue('customer_group', $customerGroup->getCustomerGroupCode());
                    $this->cache['customer_group'][$customer->getGroupId()] = $customerGroup->getCustomerGroupCode();
                }
            }
        }

        // Has this customer purchased yet + order count
        if ($this->fieldLoadingRequired('has_purchased') || $this->fieldLoadingRequired('order_count')) {
            $customerOrders = $this->orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customer->getId());

            $orderCount = $customerOrders->getSize();
            if ($orderCount > 0) {
                $this->writeValue('has_purchased', '1');
                $this->writeValue('order_count', $orderCount);
            } else {
                $this->writeValue('has_purchased', '0');
                $this->writeValue('order_count', '0');
            }
        }

        // First order date + last order date
        if ($this->fieldLoadingRequired('first_order_timestamp')) {
            $customerOrders = $this->orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customer->getId())
                ->setOrder('created_at', 'ASC');
            if ($customerOrder = $customerOrders->getFirstItem()) {
                $this->writeValue('first_order_timestamp', $this->dateHelper->convertDateToStoreTimestamp($customerOrder->getCreatedAt()));
            } else {
                $this->writeValue('first_order_timestamp', 0);
            }
        }
        if ($this->fieldLoadingRequired('last_order_timestamp')) {
            $customerOrders = $this->orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customer->getId())
                ->setOrder('created_at', 'DESC');
            if ($customerOrder = $customerOrders->getFirstItem()) {
                $this->writeValue('last_order_timestamp', $this->dateHelper->convertDateToStoreTimestamp($customerOrder->getCreatedAt()));
            } else {
                $this->writeValue('last_order_timestamp', 0);
            }
        }

        // Customer addresses
        $addressCollection = $customer->getAddressesCollection();
        if (!empty($addressCollection) && $this->fieldLoadingRequired('addresses')) {
            /** @var \Magento\Customer\Model\Address $customerAddress */
            foreach ($addressCollection as $customerAddress) {
                if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $this->writeArray = & $returnArray['addresses'][];
                } else {
                    $this->writeArray = & $returnArray['customer']['addresses'][];
                }
                $customerAddress->explodeStreetAddress();
                foreach ($customerAddress->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                // Region Code
                if ($customerAddress->getRegionId() !== NULL && $this->fieldLoadingRequired('region_code')) {
                    $this->writeValue('region_code', $customerAddress->getRegionCode());
                }
                // Country - ISO3, Full Name
                if ($customerAddress->getCountryId() !== null && ($this->fieldLoadingRequired(
                            'country_name'
                        ) || $this->fieldLoadingRequired('country_iso3'))
                ) {
                    if (!isset(self::$countryModels[$customerAddress->getCountryId()])) {
                        $country = $this->countryFactory->create();
                        $country->load($customerAddress->getCountryId());
                        self::$countryModels[$customerAddress->getCountryId()] = $country;
                    }
                    if ($this->fieldLoadingRequired('country_name')) {
                        $this->writeValue('country_name', self::$countryModels[$customerAddress->getCountryId()]->getName());
                    }
                    if ($this->fieldLoadingRequired('country_iso3')) {
                        $this->writeValue(
                            'country_iso3',
                            self::$countryModels[$customerAddress->getCountryId()]->getData('iso3_code')
                        );
                    }
                }
                if ($customerAddress->getId() === $customer->getDefaultBilling() && $customerAddress->getId() === $customer->getDefaultShipping()) {
                    $this->writeValue('address_type', 'default_billing_shipping');
                } else if ($customerAddress->getId() === $customer->getDefaultBilling()) {
                    $this->writeValue('address_type', 'default_billing');
                } else if ($customerAddress->getId() === $customer->getDefaultShipping()) {
                    $this->writeValue('address_type', 'default_shipping');
                } else {
                    $this->writeValue('address_type', 'address');
                }
            }
        }

        // Done
        return $returnArray;
    }
}