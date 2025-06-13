<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-04-07T18:18:13+00:00
 * File:          Model/Export/Entity/Invoice.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Entity;

class Invoice extends AbstractEntity
{
    protected $entityType = \Xtento\OrderExport\Model\Export::ENTITY_INVOICE;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * Invoice constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\OrderExport\Model\Export\Data $exportData
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\OrderExport\Model\Export\Data $exportData,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        parent::__construct($context, $registry, $profileFactory, $historyCollectionFactory, $exportData, $timezone, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $collection = $this->invoiceCollectionFactory->create()
            ->addAttributeToSelect('*');
        $this->collection = $collection;
        parent::_construct();
    }
}