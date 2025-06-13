<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T15:47:10+00:00
 * File:          Model/Export/Data/Order/History.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Order;

class History extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    public function getConfiguration()
    {
        return [
            'name' => 'Order Status History',
            'category' => 'Order',
            'description' => 'Export the order status history and any comments added.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO],
        ];
    }

    /**
     * @param $entityType
     * @param $collectionItem
     *
     * @return array
     */
    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreStart
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['order_status_history'];
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('order_status_history')) {
            return $returnArray;
        }

        if ($order) {
            foreach ($order->getAllStatusHistory() as $history) {
                $this->writeArray = & $returnArray['order_status_history'][];
                foreach ($history->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                    if ($key == 'created_at') {
                        $this->writeValue('created_at_timestamp', $this->dateHelper->convertDateToStoreTimestamp($value));
                    }
                }
            }
        }
        $this->writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}