<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-02-10T13:22:26+00:00
 * File:          Observer/AdminSalesOrderAddressUpdateObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Observer;

use Xtento\OrderExport\Model\Export;

class AdminSalesOrderAddressUpdateObserver extends AbstractEventObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->handleEvent($observer, self::EVENT_SALES_ORDER_ADDRESS_UPDATE_ADMIN, Export::ENTITY_ORDER);
    }
}
