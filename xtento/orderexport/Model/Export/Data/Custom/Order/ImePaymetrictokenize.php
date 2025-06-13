<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-10-15T11:56:43+00:00
 * File:          Model/Export/Data/Custom/Order/ImePaymetrictokenize.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class ImePaymetrictokenize extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * ImePaymetrictokenize constructor.
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
            'name' => 'Ime_Paymetrictokenize Data Export',
            'category' => 'Order',
            'description' => 'Export additional data stored by the Ime_Paymetrictokenize extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Ime_Paymetrictokenize',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if ($this->fieldLoadingRequired('paymetrictokenize_rows')) {
            try {
                $this->writeArray = &$returnArray['paymetrictokenize_rows']; // Write on "paymetrictokenize_rows" level

                $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
                $readAdapter = $resource->getConnection();
                $table = $resource->getTableName('paymetric');
                $binds = [
                    'orderID' => $order->getRealOrderId(),
                ];
                $dataRows = $readAdapter->fetchAll("SELECT * FROM {$table} WHERE orderID = :orderID", $binds);

                if (is_array($dataRows)) {
                    foreach ($dataRows as $dataRow) {
                        $this->writeArray = &$returnArray['paymetrictokenize_rows'][];
                        foreach ($dataRow as $key => $value) {
                            $this->writeValue($key, $value);
                        }
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