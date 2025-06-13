<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-09-17T12:42:25+00:00
 * File:          Plugin/OrderServicePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Plugin;

use \Magento\Sales\Model\Service\OrderService;
use \Magento\Sales\Api\Data\OrderInterface;

class OrderServicePlugin
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * OrderServicePlugin constructor.
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * Dispatch custom event to process/export order once it has been saved in DB
     *
     * @param OrderService $subject
     * @param \Closure $proceed
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function aroundPlace(OrderService $subject, \Closure $proceed, OrderInterface $order)
    {
        $this->eventManager->dispatch('xtento_orderexport_sales_order_service_place_before', ['order' => $order]);
        $return = $proceed($order);
        $this->eventManager->dispatch('xtento_orderexport_sales_order_service_place_after', ['order' => $return]);
        return $return;
    }
}