<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-08-30T12:19:21+00:00
 * File:          Model/Export/Data/Custom/Order/MagetrendOrderAttribute.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class MagetrendOrderAttribute extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * MagetrendOrderAttribute constructor.
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
            'name' => 'Magetrend Order Attribute Export',
            'category' => 'Order',
            'description' => 'Export additional order attributes stored by the Magetrend Order Attributes extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Magetrend_OrderAttribute',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if ($this->fieldLoadingRequired('magetrend_orderattribute')) {
            try {
                $this->writeArray = &$returnArray['magetrend_orderattribute'];

                $dataResolver = $this->objectManager->create('\Magetrend\OrderAttribute\Model\DataResolver');
                $dataResolver = $dataResolver->resetResolver();
                $additionalAttributes = $dataResolver->getOrderValues($order->getId());

                foreach ($additionalAttributes as $additionalAttribute) {
                    $this->writeValue($additionalAttribute->getAttributeCode(), $additionalAttribute->getRawValueText());
                }
            } catch (\Exception $e) {

            }
            $this->writeArray = &$returnArray;
        }

        // Done
        return $returnArray;
    }
}