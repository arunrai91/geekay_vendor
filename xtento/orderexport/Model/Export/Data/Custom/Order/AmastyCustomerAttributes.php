<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-06-14T11:03:35+00:00
 * File:          Model/Export/Data/Custom/Order/AmastyCustomerAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class AmastyCustomerAttributes extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AmastyDeliveryDate constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->objectManager = $objectManager;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Amasty Customer Attributes Export',
            'category' => 'Order',
            'description' => 'Export (guest) customer attributes of Amasty_CustomerAttributes extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Amasty_CustomerAttributes',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['customer']; // Write on "customer" level

        if (!$this->fieldLoadingRequired('customer')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();
        if ($order->getCustomerId()) {
            // Just for guests
            return $returnArray;
        }

        try {
            $guestAttributes = $this->objectManager->create('Amasty\CustomerAttributes\Model\Customer\GuestAttributesFactory')->create();
            $guestAttributes->loadByOrderId($order->getId());
            if ($guestAttributes->getId()) {
                foreach ($guestAttributes->getData() as $key => $value) {
                    if ($key == 'id' || $key == 'order_id') {
                        continue;
                    }
                    $this->writeValue($key, $value);
                }
            }
        } catch (\Exception $e) {

        }

        // Done
        return $returnArray;
    }
}