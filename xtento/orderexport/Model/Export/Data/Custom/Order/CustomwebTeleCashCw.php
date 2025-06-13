<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-05-14T13:32:09+00:00
 * File:          Model/Export/Data/Custom/Order/CustomwebTeleCashCw.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class CustomwebTeleCashCw extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * CustomwebTeleCashCw constructor.
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
            'name' => 'Customweb TeleCashCw Transaction Data Export',
            'category' => 'Order',
            'description' => 'Export additional data stored by the Customweb TeleCashCw extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Customweb_TeleCashCw',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if ($this->fieldLoadingRequired('customweb_telecashcw_transaction')) {
            try {
                $this->writeArray = &$returnArray['customweb_telecashcw_transaction'];

                $order_id = $order->getId();

                $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
                $readAdapter = $resource->getConnection();
                $table = $resource->getTableName('customweb_telecashcw_transaction');

                $dataRow = $readAdapter->fetchRow("SELECT * FROM {$table} WHERE order_id = $order_id");

                foreach ($dataRow as $key => $value) {
                    $this->writeValue($key, $value);
                }
            } catch (\Exception $e) {

            }
            $this->writeArray = &$returnArray;
        }

        // Done
        return $returnArray;
    }
}