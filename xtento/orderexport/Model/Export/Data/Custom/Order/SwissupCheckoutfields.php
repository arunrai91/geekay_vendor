<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2021-01-19T19:45:13+00:00
 * File:          Model/Export/Data/Custom/Order/SwissupCheckoutfields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class SwissupCheckoutfields extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * SwissupCheckoutfields constructor.
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
            'name' => 'Swissup Checkout Fields Export',
            'category' => 'Order',
            'description' => 'Export custom checkout fields of Swissup Checkout extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Swissup_CheckoutFields',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = &$returnArray['swissup_checkoutfields']; // Write on "swissup_checkoutfields" level

        if (!$this->fieldLoadingRequired('swissup_checkoutfields')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            $storeId = $order->getStore()->getId();
            $fields = $this->objectManager->get('\Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory')
                ->create()
                ->addEmptyValueFilter()
                ->addOrderFilter($order->getId())
                ->addStoreLabel($storeId);

            foreach ($fields as $field) {
                if ($field->getFrontendInput() == 'date') {
                    $formattedDate = $this->objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')->formatDate(
                        $this->objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')->scopeDate(
                            $order->getStore(),
                            $field->getValue()
                        ),
                        \IntlDateFormatter::MEDIUM,
                        false
                    );
                    $field->setValue($formattedDate);
                } elseif ($field->getFrontendInput() == 'boolean') {
                    $yesnoValues = $this->objectManager->get('\Magento\Config\Model\Config\Source\YesnoFactory')->create()->toArray();
                    $field->setValue($yesnoValues[$field->getValue()]);
                } else if ($field->getFrontendInput() == 'select' ||
                    $field->getFrontendInput() == 'multiselect')
                {
                    $options = $this->objectManager->get('\Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory')->create()
                        ->setStoreFilter($storeId)
                        ->setIdFilter(explode(',', $field->getValue()))
                        ->getColumnValues('value');

                    if (is_array($options)) {
                        $field->setValue(implode(",", $options));
                    } else {
                        $field->setValue($options);
                    }
                }
            }

            foreach ($fields as $field) {
                $this->writeArray = &$returnArray['swissup_checkoutfields'][];
                foreach ($field->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
            }
        } catch (\Exception $e) {

        }
        $this->writeArray = &$returnArray;

        // Done
        return $returnArray;
    }
}