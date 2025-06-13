<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-11-25T20:45:30+00:00
 * File:          Model/Export/Data/Custom/Order/AmastyGiftWrap.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class AmastyGiftWrap extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AmastyDeliveryDate constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->objectManager = $objectManager;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Amasty Gift Wrap information export',
            'category' => 'Order',
            'description' => 'Export gift wrap data stored by Amasty GiftWrap',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Amasty_GiftWrap',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['amasty_giftwrap_items']; // Write on "amasty_giftwrap_items" level

        if (!$this->fieldLoadingRequired('amasty_giftwrap_items')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            $saleDataResource = $this->objectManager->create('Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface');
            $saleDataResource->loadItemsData(
                'amasty_giftwrap_order_wrap',
                'order_id',
                $order
            );

            if ($order->getWrapItems()) {
                foreach ($order->getWrapItems() as $wrapItem) {
                    $this->writeArray = & $returnArray['amasty_giftwrap_items'][];
                    foreach ($wrapItem->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                }
            }
        } catch (\Exception $e) {

        }
        $this->writeArray = &$returnArray;

        // Done
        return $returnArray;
    }
}