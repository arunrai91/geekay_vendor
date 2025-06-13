<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-08-07T11:38:22+00:00
 * File:          Model/Export/Data/Custom/Order/TigPostnl.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class TigPostnl extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * Directory country models
     *
     * @var \Magento\Directory\Model\Country[]
     */
    protected static $countryModels = [];

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * TigPostnl constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->objectManager = $objectManager;
        $this->countryFactory = $countryFactory;
        $this->localeDate = $localeDate;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'TIG_PostNL Pakjegemak Address Export',
            'category' => 'Order',
            'description' => 'Export the Pakjegemak address saved by the TIG_PostNL extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'TIG_PostNL',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if ($this->fieldLoadingRequired('pakjegemak_order')) {
            try {
                $this->writeArray = & $returnArray['pakjegemak_order'];
                $postNLOrder = $this->objectManager->create('\TIG\PostNL\Model\OrderFactory')->create();
                $postNLOrder->load($order->getId(), 'order_id');

                if ($postNLOrder->getId()) {
                    foreach ($postNLOrder->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                    $this->writeValue('delivery_date_formatted', $this->localeDate->formatDate($postNLOrder->getDeliveryDate(), \IntlDateFormatter::LONG, true));
                    $this->writeValue('delivery_date_timestamp', $this->dateHelper->convertDateToStoreTimestamp($postNLOrder->getDeliveryDate()));

                    // PakjeGemak address is stored in "shipping address"
                }
            } catch (\Exception $e) {

            }
        }

        // Done
        return $returnArray;
    }
}