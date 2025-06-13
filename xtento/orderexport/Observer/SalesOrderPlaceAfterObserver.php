<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-04-17T13:03:55+00:00
 * File:          Observer/SalesOrderPlaceAfterObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Observer;

use Xtento\OrderExport\Model\Export;

class SalesOrderPlaceAfterObserver extends AbstractEventObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->handleEvent($observer, self::EVENT_SALES_ORDER_PLACE_AFTER, Export::ENTITY_ORDER);
    }
}
