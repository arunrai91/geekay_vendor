<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-04-17T13:03:38+00:00
 * File:          Observer/SalesOrderInvoiceRegisterObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Observer;

use Xtento\OrderExport\Model\Export;

class SalesOrderInvoiceRegisterObserver extends AbstractEventObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->handleEvent($observer, self::EVENT_SALES_ORDER_INVOICE_REGISTER, Export::ENTITY_INVOICE);
        $this->handleEvent($observer, self::EVENT_SALES_ORDER_INVOICE_REGISTER, Export::ENTITY_ORDER);
    }
}
