<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-05-01T12:54:11+00:00
 * File:          Model/Export/Data/Custom/Order/WyomindAdvancedInventory.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class WyomindAdvancedInventory extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * WyomindAdvancedInventory constructor.
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
            'name' => 'Wyomind_AdvancedInventory Data Export',
            'category' => 'Order',
            'description' => 'Export assigned warehouses / warehouse information of the AdvancedInventory extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Wyomind_AdvancedInventory',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        // Match like this:
        // <xsl:variable name="orderItemId" select="item_id" />
        // <xsl:value-of select="../../wyomind_advancedinventory_assignations/wyomind_advancedinventory_assignation[item_id=$orderItemId]/place_id"/>

        if ($this->fieldLoadingRequired('wyomind_advancedinventory_assignations')) {
            try {
                $this->writeArray = &$returnArray['wyomind_advancedinventory_assignations']; // Write on "wyomind_advancedinventory_assignations" level

                $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
                $readAdapter = $resource->getConnection();
                $table = $resource->getTableName('advancedinventory_assignation');
                $placeTable = $resource->getTableName('pointofsale');

                foreach ($order->getAllItems() as $item) {
                    $binds = [
                        'itemId' => $item->getId(),
                    ];
                    $dataRows = $readAdapter->fetchAll("SELECT * FROM {$table} WHERE item_id = :itemId", $binds);

                    if (is_array($dataRows)) {
                        foreach ($dataRows as $dataRow) {
                            if ($dataRow['qty_assigned'] <= 0) {
                                continue;
                            }
                            $this->writeArray = & $returnArray['wyomind_advancedinventory_assignations'][];
                            foreach ($dataRow as $key => $value) {
                                $this->writeValue($key, $value);
                            }
                            $placeId = $dataRow['place_id'];
                            if ($placeId) {
                                $binds = [
                                    'placeId' => $placeId
                                ];
                                $dataRowPlace = $readAdapter->fetchRow("SELECT * FROM {$placeTable} WHERE place_id = :placeId", $binds);
                                foreach ($dataRowPlace as $key => $value) {
                                    $this->writeValue($key, $value);
                                }
                            }
                        }
                    }
                }

                $this->writeArray = &$returnArray['wyomind_advancedinventory_items']; // Write on "wyomind_advancedinventory_items" level
                $collection = $this->objectManager->create('\Wyomind\AdvancedInventory\Model\ResourceModel\Order\Item\Collection');
                $data = $collection->getAssignationByOrderId($order->getId());

                foreach ($data as $row) {
                    $this->writeArray = & $returnArray['wyomind_advancedinventory_items'][];
                    foreach ($row->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                }

            } catch (\Exception $e) {

            }
            $this->writeArray = &$returnArray;
        }

        // Done
        return $returnArray;
    }
}