<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:14:21+00:00
 * File:          Model/Export/Data/Shipment/Tracking.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Shipment;

class Tracking extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    public function getConfiguration()
    {
        return [
            'name' => 'Tracking information',
            'category' => 'Shipment',
            'description' => 'Export tracking information for shipments exported.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT],
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['tracks'];
        // Fetch fields to export
        $shipment = $collectionItem->getObject();
        if (!$shipment && !$collectionItem->getId()) {
            return $returnArray;
        }
        if (!$shipment) {
            $shipment = $collectionItem;
        }

        if (!$this->fieldLoadingRequired('tracks') && !$this->fieldLoadingRequired('tracking_numbers') && !$this->fieldLoadingRequired('carriers')) {
            return $returnArray;
        }

        $tracks = $shipment->getAllTracks();

        if ($tracks) {
            $trackingNumbers = [];
            $carrierNames = [];
            foreach ($tracks as $track) {
                $this->writeArray = & $returnArray['tracks'][];
                foreach ($track->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                    if ($key == 'number') {
                        $this->writeValue('track_number', $value);
                        $trackingNumbers[] = $value;
                    }
                    if ($key == 'track_number') {
                        $this->writeValue('number', $value);
                        $trackingNumbers[] = $value;
                    }
                    if ($key == 'title') {
                        $carrierNames[] = $value;
                    }
                }
            }
            $trackingNumbers = array_unique($trackingNumbers);
            $this->writeArray = & $returnArray;
            $this->writeValue('tracking_numbers', implode(",", $trackingNumbers));
            $this->writeValue('carriers', implode(",", $carrierNames));
        }

        // Done
        return $returnArray;
    }
}