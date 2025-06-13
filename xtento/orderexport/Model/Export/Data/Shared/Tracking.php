<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:23:56+00:00
 * File:          Model/Export/Data/Shared/Tracking.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Shared;

class Tracking extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var \Xtento\OrderExport\Model\Export\Data\Shipment\Tracking
     */
    protected $exportTracking;

    /**
     * Tracking constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Xtento\OrderExport\Model\Export\Data\Shipment\Tracking $exportTracking
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Xtento\OrderExport\Model\Export\Data\Shipment\Tracking $exportTracking,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->exportTracking = $exportTracking;
    }


    public function getConfiguration()
    {
        return [
            'name' => 'Tracking information',
            'category' => 'Order',
            'description' => 'Export information about tracking numbers assigned to child shipments.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO],
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

        $shipments = $this->shipmentCollectionFactory->create()
            ->addAttributeToFilter('order_id', $order->getId())
            ->load();

        foreach ($shipments as $shipment) {
            $exportClass = $this->exportTracking; // Singleton
            $exportClass->setProfile($this->getProfile());
            $exportClass->setShowEmptyFields($this->getShowEmptyFields());
            $returnData = $exportClass->getExportData(\Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, $shipment);
            if (is_array($returnData) && !empty($returnData)) {
                $returnArray = array_merge_recursive($returnArray, $returnData);
            }
        }
        // Done
        return $returnArray;
    }
}