<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2022-04-24T18:33:27+00:00
 * File:          Observer/AbstractEventObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Observer;

use Xtento\OrderExport\Model\Export;

class AbstractEventObserver extends \Xtento\OrderExport\Model\AbstractAutomaticExport
{
    protected $events = [];
    public static $exportedIds = [];

    // Magento default events
    const EVENT_SALES_ORDER_SAVE_AFTER = 1;
    const EVENT_SALES_ORDER_PLACE_AFTER = 2;
    const EVENT_SALES_ORDER_PAYMENT_PLACE_END = 3;
    const EVENT_SALES_ORDER_INVOICE_REGISTER = 4;
    const EVENT_SALES_ORDER_INVOICE_PAY = 5;
    const EVENT_SALES_ORDER_SHIPMENT_SAVE_AFTER = 6;
    const EVENT_SALES_ORDER_CREDITMEMO_SAVE_AFTER = 7;
    const EVENT_SALES_ORDER_SERVICE_PLACE_AFTER = 8;
    const EVENT_SALES_ORDER_ADDRESS_UPDATE_ADMIN = 9;
    // Customer events
    const EVENT_CUSTOMER_SAVE_AFTER = 20;
    const EVENT_CUSTOMER_AFTER_REGISTRATION = 21;
    const EVENT_CUSTOMER_ADDRESS_SAVE_AFTER = 22;
    // Third party events
    //const EVENT_PRODUCTRETURN_ORDER_CREATED_FOR_RMA = 30;

    /**
     * Get export events
     * 
     * @param bool|false $entity
     * @param bool|false $allEvents
     * @return array
     */
    public function getEvents($entity = false, $allEvents = false)
    {
        $events = [];
        // Events where order information can be exported
        if ($allEvents || $entity == Export::ENTITY_ORDER) {
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_SERVICE_PLACE_AFTER] = [
                'event' => 'xtento_orderexport_sales_order_service_place_after',
                'label' => __('After order creation (Event: xtento_orderexport_sales_order_service_place_after)'),
                'method' => 'getOrder()',
                'force_collection_item' => true
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_PLACE_AFTER] = [
                'event' => 'sales_order_place_after',
                'label' => __('Alternative Event: After order creation (Event: sales_order_place_after)'),
                'method' => 'getOrder()',
                'force_collection_item' => true
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_SAVE_AFTER] = [
                'event' => 'sales_order_save_after',
                'label' => __('After order modification (Event: sales_order_save_after)'),
                'method' => 'getOrder()',
                'force_collection_item' => true
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_PAYMENT_PLACE_END] = [
                'event' => 'sales_order_payment_place_end',
                'label' => __('After order placement completed (Event: sales_order_payment_place_end)'),
                'method' => 'getPayment()->getOrder()',
                'force_collection_item' => true
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_INVOICE_REGISTER] = [
                'event' => 'sales_order_invoice_register',
                'label' => __('After invoice creation (Event: sales_order_invoice_register)'),
                'method' => 'getInvoice()->getOrder()'
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_INVOICE_PAY] = [
                'event' => 'sales_order_invoice_pay',
                'label' => __('After invoice has been paid (Event: sales_order_invoice_pay)'),
                'method' => 'getInvoice()->getOrder()'
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_SHIPMENT_SAVE_AFTER] = [
                'event' => 'sales_order_shipment_save_after',
                'label' => __('After shipment creation (Event: sales_order_shipment_save_after)'),
                'method' => 'getShipment()->getOrder()'
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_CREDITMEMO_SAVE_AFTER] = [
                'event' => 'sales_order_creditmemo_save_after',
                'label' => __('After credit memo creation (Event: sales_order_creditmemo_save_after)'),
                'method' => 'getCreditmemo()->getOrder()'
            ];
            $events[Export::ENTITY_ORDER][self::EVENT_SALES_ORDER_ADDRESS_UPDATE_ADMIN] = [
                'event' => 'admin_sales_order_address_update',
                'label' => __('After order address is edited in admin (Event: admin_sales_order_address_update)'),
                'method' => 'getOrderId()'
            ];
        }
        // Events where invoice information can be exported
        if ($allEvents || $entity == Export::ENTITY_INVOICE) {
            $events[Export::ENTITY_INVOICE][self::EVENT_SALES_ORDER_INVOICE_REGISTER] = [
                'event' => 'sales_order_invoice_register',
                'label' => __('After invoice creation (Event: sales_order_invoice_register)'),
                'method' => 'getInvoice()'
            ];
            $events[Export::ENTITY_INVOICE][self::EVENT_SALES_ORDER_INVOICE_PAY] = [
                'event' => 'sales_order_invoice_pay',
                'label' => __('After invoice has been paid (Event: sales_order_invoice_pay)'),
                'method' => 'getInvoice()'
            ];
        }
        // Events where shipment information can be exported
        if ($allEvents || $entity == Export::ENTITY_SHIPMENT) {
            $events[Export::ENTITY_SHIPMENT][self::EVENT_SALES_ORDER_SHIPMENT_SAVE_AFTER] = [
                'event' => 'sales_order_shipment_save_after',
                'label' => __('After shipment creation (Event: sales_order_shipment_save_after)'),
                'method' => 'getShipment()'
            ];
        }
        // Events where credit memo information can be exported
        if ($allEvents || $entity == Export::ENTITY_CREDITMEMO) {
            $events[Export::ENTITY_CREDITMEMO][self::EVENT_SALES_ORDER_CREDITMEMO_SAVE_AFTER] = [
                'event' => 'sales_order_creditmemo_save_after',
                'label' => __('After credit memo creation (Event: sales_order_creditmemo_save_after)'),
                'method' => 'getCreditmemo()'
            ];
        }
        // Events where customer information can be exported
        if ($allEvents || $entity == Export::ENTITY_CUSTOMER) {
            $events[Export::ENTITY_CUSTOMER][self::EVENT_CUSTOMER_AFTER_REGISTRATION] = [
                'event' => 'customer_register_success',
                'label' => __('After customer signs up'),
                'method' => 'getCustomer()',
                'force_collection_item' => true
            ];
            $events[Export::ENTITY_CUSTOMER][self::EVENT_CUSTOMER_SAVE_AFTER] = [
                'event' => 'customer_save_after',
                'label' => __('After customer account gets modified'),
                'method' => 'getCustomer()',
                'force_collection_item' => true
            ];
            $events[Export::ENTITY_CUSTOMER][self::EVENT_CUSTOMER_ADDRESS_SAVE_AFTER] = [
                'event' => 'customer_address_save_after',
                'label' => __('After customer address gets modified'),
                'method' => 'getCustomerAddress()->getCustomer()',
                'force_collection_item' => true
            ];
        }
        // Third party events
        // None at this time
        return $events;
    }

    /*
     *  Third party events
     */
    // None at this time

    /*
     * Code handling events
     */
    protected function handleEvent(\Magento\Framework\Event\Observer $observer, $eventId = 0, $entity = 0)
    {
        try {
            if (!$this->moduleHelper->isModuleEnabled() || !$this->moduleHelper->isModuleProperlyInstalled()) {
                return;
            }
            if ($this->_registry->registry('do_not_process_event_exports') === true) {
                return;
            }
            $event = $observer->getEvent();

            // Load profiles which are listening for this event
            $profileCollection = $this->profileCollectionFactory->create()
                ->addFieldToFilter('enabled', 1) // Profile enabled
                ->addFieldToFilter('entity', $entity)
                ->addFieldToFilter('event_observers', ['like' => '%' . $eventId . '%']); // Event enabled "pre-check"
            foreach ($profileCollection as $profile) {
                $profileId = $profile->getId();
                $eventObservers = explode(",", $profile->getEventObservers());
                if (!in_array($eventId, $eventObservers)) {
                    continue; // Not enabled for this event
                }
                if (!isset(self::$exportedIds[$profileId])) {
                    self::$exportedIds[$profileId] = [];
                    // Note: $exportedIds checking whether item has been exported seems to be broken. getId() for events in M2 return "null", unlike M1.
                }
                $entityIdField = 'main_table.entity_id';
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $entityIdField = 'entity_id';
                }
                $exportObject = $this->getExportObject($entity, $event, $eventId);
                if (!is_object($exportObject) && $eventId == self::EVENT_SALES_ORDER_ADDRESS_UPDATE_ADMIN) {
                    $exportObjectId = $exportObject;
                    $entityIdField = 'entity_id';
                } else {
                    $exportObjectId = $exportObject->getId();
                    if (!$exportObjectId && $exportObject->getIncrementId()) {
                        $exportObjectId = $exportObject->getIncrementId();
                        $entityIdField = 'increment_id';
                    }
                }
                if ($exportObject) {
                    if (!in_array($exportObjectId, self::$exportedIds[$profileId])) {
                        $exportModel = $this->exportFactory->create()->setProfile($profile);
                        if (isset($this->events[$entity][$eventId]['force_collection_item']) && $this->events[$entity][$eventId]['force_collection_item'] === true) {
                            $filters = $this->addProfileFilters($profile);
                            if ($exportModel->eventExport($filters, $exportObject)) {
                                // Has been exported in this execution.. do not export again in the same execution.
                                if ($exportObjectId) {
                                    array_push(self::$exportedIds[$profileId], $exportObjectId);
                                }
                                $this->_registry->registry('orderexport_log')->setExportEvent(
                                    $this->events[$entity][$eventId]['event']
                                )->save();
                            }
                        } else {
                            if ($exportObjectId) {
                                $filters = [[$entityIdField => $exportObjectId]];
                                $filters = array_merge($filters, $this->addProfileFilters($profile));
                                if ($exportModel->eventExport($filters)) {
                                    // Has been exported in this execution.. do not export again in the same execution.
                                    array_push(self::$exportedIds[$profileId], $exportObjectId);
                                    $this->_registry->registry('orderexport_log')->setExportEvent(
                                        $this->events[$entity][$eventId]['event']
                                    )->save();
                                }
                            }
                        }
                    }
                } else {
                    $this->xtentoLogger->warning('Event handler for event '.$eventId.': Could not find export object.');
                }
            }
        } catch (\Exception $e) {
            #echo $e->getTraceAsString(); die();
            $this->xtentoLogger->warning('Event handler exception for event '.$eventId.': '.$e->getMessage());
            return;
        }
    }

    protected function getExportObject($entity, $event, $eventId)
    {
        if (empty($this->events)) {
            $this->events = $this->getEvents(false, true);
        }
        if (isset($this->events[$entity][$eventId]) && isset($this->events[$entity][$eventId]['method'])) {
            $eventMethods = explode("->", str_replace('()', '', $this->events[$entity][$eventId]['method']));
            if (count($eventMethods) == 1) {
                return $event->{$eventMethods[0]}();
            } else if (count($eventMethods) == 2) {
                return $event->{$eventMethods[0]}()->{$eventMethods[1]}();
            }
        }
        return false;
    }
}
