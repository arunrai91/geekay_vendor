<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2017-05-15T15:34:34+00:00
 * File:          Model/Export/Data/Custom/Order/BssCheckoutCustomField.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class BssCheckoutCustomField extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * BssCheckoutCustomField constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        JsonHelper $jsonHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->jsonHelper = $jsonHelper;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'BSS Checkout Custom Field Export',
            'category' => 'Order',
            'description' => 'Export custom order attributes of BSS Checkout Custom Field extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Bss_CheckoutCustomField',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['bss_checkoutcustomfields']; // Write on "bss_checkoutcustomfields" level

        if (!$this->fieldLoadingRequired('bss_checkoutcustomfields')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            $bssCustomfieldJson = $order->getData('bss_customfield');
            $bssCustomfield = $this->jsonHelper->jsonDecode($bssCustomfieldJson);
            foreach($bssCustomfield as $key => $field)
            {
                $this->writeValue($key, $field['value']);
            }
        } catch (\Exception $e) {

        }

        // Done
        return $returnArray;
    }
}