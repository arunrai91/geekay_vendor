<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-05-27T10:22:22+00:00
 * File:          Model/Export/Data/Custom/Order/AheadworksGiftCard.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class AheadworksGiftCard extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AmastyGiftCard constructor.
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
            'name' => 'Aheadworks Gift Card Export',
            'category' => 'Order',
            'description' => 'Export data stored by the Aheadworks Gift Card extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Aheadworks_Giftcard',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = &$returnArray['aheadworks_giftcards']; // Write on "aheadworks_giftcards" level

        if (!$this->fieldLoadingRequired('aheadworks_giftcards')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            $giftcardOrderItems = $this->objectManager->create('\Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Order\CollectionFactory')->create()->addFieldToFilter('order_id', $order->getEntityId())->load()->getItems();
            foreach ($giftcardOrderItems as $giftcardOrderItem) {
                $this->writeArray = & $returnArray['aheadworks_giftcards'][];
                foreach ($giftcardOrderItem->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                $giftcard = $this->objectManager->create('\Aheadworks\Giftcard\Model\GiftcardFactory')->create()->load($giftcardOrderItem->getGiftcardId());
                if ($giftcard && $giftcard->getId()) {
                    $this->writeArray = & $this->writeArray['giftcard_data'];
                    foreach ($giftcard->getData() as $key => $value) {
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