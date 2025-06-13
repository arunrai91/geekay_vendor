<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-05-20T15:30:55+00:00
 * File:          Model/ResourceModel/History/Collection.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->request = $request;
        $this->registry = $registry;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init('Xtento\OrderExport\Model\History', 'Xtento\OrderExport\Model\ResourceModel\History');
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        if ($this->request->getControllerName() == 'history' ||
            ($this->request->getControllerName() == 'profile' && $this->request->getActionName() == 'history')
        ) {
            $this->getSelect()->joinLeft(
                ['profile' => $this->getTable('xtento_orderexport_profile')],
                'main_table.profile_id = profile.profile_id',
                ['concat(profile.name," (ID: ", profile.profile_id,")") as profile', 'profile.entity', 'profile.name']
            );
            $this->getSelect()->joinLeft(
                ['order' => $this->getTable('sales_order')],
                'main_table.entity_id = order.entity_id and profile.entity="order"',
                ['order.increment_id as order_increment_id']
            );
            $this->getSelect()->joinLeft(
                ['invoice' => $this->getTable('sales_invoice')],
                'main_table.entity_id = invoice.entity_id and profile.entity="invoice"',
                ['invoice.increment_id as invoice_increment_id']
            );
            $this->getSelect()->joinLeft(
                ['shipment' => $this->getTable('sales_shipment')],
                'main_table.entity_id = shipment.entity_id and profile.entity="shipment"',
                ['shipment.increment_id as shipment_increment_id']
            );
            $this->getSelect()->joinLeft(
                ['creditmemo' => $this->getTable('sales_creditmemo')],
                'main_table.entity_id = creditmemo.entity_id and profile.entity="creditmemo"',
                ['creditmemo.increment_id as creditmemo_increment_id']
            );

            if ($this->request->getParam('id', false)) {
                $this->addFieldToFilter('main_table.profile_id', intval($this->request->getParam('id')));
            }

            /*if ($this->registry->registry('orderexport_profile')->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_EERMA) {
                $this->getSelect()->joinLeft(['object' => $this->getTable('magento_rma')], 'main_table.entity_id = object.entity_id', array('object.entity_id'));
            }*/
        }

        /* Old module:
        if ($this->_getProfile()->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('sales/' . $this->_getProfile()->getEntity())), 'main_table.entity_id = object.entity_id', array('object.entity_id'));
        } else if ($this->_getProfile()->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_AWRMA) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('awrma/entity')), 'main_table.entity_id = object.id', array('object.id'));
        } else if ($this->_getProfile()->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('ProductReturn/rma')), 'main_table.entity_id = object.rma_id', array('object.rma_id'));
        } else if ($this->_getProfile()->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
            $collection->getSelect()->joinLeft(array('object' => $collection->getTable('customer/entity')), 'main_table.entity_id = object.entity_id', array('object.entity_id'));
        } else {
            if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.4.0.1', '>')) {
                $collection->getSelect()->joinLeft(array('object' => $collection->getTable('sales/' . $this->_getProfile()->getEntity())), 'main_table.entity_id = object.entity_id', array('object.increment_id'));
            }
        }
        */
        return $this;
    }
}