<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-01-22T16:29:19+00:00
 * File:          Model/Export/Entity/Collection/Item.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Entity\Collection;

class Item extends \Magento\Framework\DataObject
{
    public $collectionItem;
    public $collectionSize;
    public $currItemNo;

    public function __construct($collectionItem, $entityType, $currItemNo, $collectionCount)
    {
        parent::__construct();
        $this->collectionItem = $collectionItem;
        $this->collectionSize = $collectionCount;
        $this->currItemNo = $currItemNo;
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
            $this->setOrder($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_INVOICE) {
            $this->setOrder($collectionItem->getOrder());
            $this->setInvoice($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT) {
            $this->setOrder($collectionItem->getOrder());
            $this->setShipment($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO) {
            $this->setOrder($collectionItem->getOrder());
            $this->setCreditmemo($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
            $this->setOrder($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_EERMA) {
            // Load order associated to RMA
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Due to existing structures, must be used here
            $order = $objectManager->get('\Magento\Sales\Model\OrderFactory')->create()->load(strval($collectionItem->getOrderId()));
            $this->setOrder($order);
            $collectionItem->setAllItems($collectionItem->getItemsForDisplay());
        }
    }

    public function getObject()
    {
        return $this->collectionItem;
    }
}