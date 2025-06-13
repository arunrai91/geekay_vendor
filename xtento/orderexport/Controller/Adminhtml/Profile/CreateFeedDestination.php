<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-01-08T15:20:29+00:00
 * File:          Controller/Adminhtml/Profile/CreateFeedDestination.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Profile;

use Magento\Store\Model\StoreManagerInterface;

class CreateFeedDestination extends \Xtento\OrderExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Xtento\OrderExport\Model\ResourceModel\Destination\CollectionFactory
     */
    protected $destinationCollectionFactory;

    /**
     * @var \Xtento\OrderExport\Model\DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * CreateFeedDestination constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\Destination\CollectionFactory $destinationCollectionFactory
     * @param \Xtento\OrderExport\Model\DestinationFactory $destinationFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ResourceModel\Destination\CollectionFactory $destinationCollectionFactory,
        \Xtento\OrderExport\Model\DestinationFactory $destinationFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->destinationCollectionFactory = $destinationCollectionFactory;
        $this->destinationFactory = $destinationFactory;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $dateFilter, $entityHelper, $profileFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        // Configuration
        $destinationName = __('Wizard Export Folder');
        $destinationPath = './var/salesexport/';
        $testResult = true;

        // Check if destination already exists
        $destinationCollection = $this->destinationCollectionFactory->create();
        $destinationCollection->addFieldToFilter('name', $destinationName);
        if ($destinationCollection->count() > 0) {
            $destinationId = $destinationCollection->getFirstItem()->getId();
        } else {
            // Create destination
            $destination = $this->destinationFactory->create();
            $destination->setType(\Xtento\OrderExport\Model\Destination::TYPE_LOCAL);
            $destination->setName($destinationName);
            $destination->setPath($destinationPath);
            $destination->setLastModification(time());
            $destination->save();

            // Test destination
            $this->registry->register('orderexport_destination', $destination, true);
            $testResult = $this->testConnection();

            // Get destination ID
            $destinationId = $destination->getId();
        }

        if ($testResult === true) {
            $resultPage->setData(['success' => true, 'destination_id' => $destinationId]);
        } else {
            $resultPage->setData(['success' => false, 'destination_id' => $destinationId, 'warning' => $testResult]);
        }
        return $resultPage;
    }

    protected function testConnection()
    {
        $destination = $this->registry->registry('orderexport_destination');
        $testResult = $this->_objectManager->create(
            '\Xtento\OrderExport\Model\Destination\\' . ucfirst($destination->getType())
        )->setDestination($destination)->testConnection();
        if (!$testResult->getSuccess()) {
            return $testResult->getMessage();
        }
        return true;
    }
}
