<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-04-07T18:18:13+00:00
 * File:          Model/Export/Entity/Customer.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Entity;

class Customer extends AbstractEntity
{
    protected $entityType = \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Customer constructor.
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
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
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
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
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context, $registry, $profileFactory, $historyCollectionFactory, $exportData, $timezone, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $collection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*');
        $this->collection = $collection;
        parent::_construct();
    }

    public function setCollectionFilters($filters)
    {
        foreach ($filters as $filter) {
            foreach ($filter as $attribute => $filterArray) {
                if ($attribute == 'increment_id') {
                    $attribute = 'entity_id';
                }
                $this->collection->addAttributeToFilter($attribute, $filterArray);
            }
        }
        return $this->collection;
    }
}