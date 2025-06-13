<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-11-06T13:10:21+00:00
 * File:          Model/Export/Data/Custom/Order/XtentoCustomAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class XtentoCustomAttributes extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * XtentoCustomAttributes constructor.
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
            'name' => 'XTENTO Custom Attributes Additional Data Export',
            'category' => 'Order',
            'description' => 'Export additional data for custom attributes created using the XTENTO Order/Customer Attributes extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Xtento_CustomAttributes',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if ($this->fieldLoadingRequired('xtento_customattributes_order_files')) {
            try {
                $this->writeArray = &$returnArray['xtento_customattributes_order_files'];

                $filters = [
                    $this->objectManager->create('Magento\Framework\Api\FilterBuilder')
                        ->setField('type_id')
                        ->setValue('order_field')
                        ->create(),
                    $this->objectManager->create('Magento\Framework\Api\FilterBuilder')
                        ->setField('frontend_input')
                        ->setValue('file')
                        ->create()
                ];

                $fields = $this->objectManager->create('\Xtento\CustomAttributes\Helper\Data')->createFields($filters);
                if (!empty($fields) && isset($fields['order_field']) && is_array($fields['order_field'])) {
                    foreach ($fields['order_field'] as $attributeCode => $fieldData) {
                        $filename = $this->objectManager->get('\Magento\Framework\Url')->getUrl('xtento_customattributes/index/download/', ['file' => $order->getData($attributeCode)]);
                        $this->writeValue($attributeCode, $filename);
                    }
                }
            } catch (\Exception $e) {

            }
            $this->writeArray = &$returnArray;
        }

        // Done
        return $returnArray;
    }
}