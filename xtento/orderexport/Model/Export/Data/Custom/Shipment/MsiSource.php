<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-04-16T09:58:17+00:00
 * File:          Model/Export/Data/Custom/Shipment/MsiSource.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Shipment;

use Xtento\OrderExport\Model\Export;

class MsiSource extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * MsiSource constructor.
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
            'name' => 'Magento MSI Source',
            'category' => 'Shipment',
            'description' => 'Export MSI source for shipment',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_SHIPMENT],
            'third_party' => true,
            'depends_module' => 'Magento_InventoryShipping',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = &$returnArray;

        // Fetch fields to export
        $shipment = $collectionItem->getShipment();

        $msiSourceCode = $this->objectManager->get('Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId')->execute($shipment->getId());
        $this->writeValue('msi_source_code', $msiSourceCode);

        // Done
        return $returnArray;
    }
}