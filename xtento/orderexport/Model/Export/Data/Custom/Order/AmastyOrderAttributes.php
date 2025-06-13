<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-07-10T11:18:39+00:00
 * File:          Model/Export/Data/Custom/Order/AmastyOrderAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class AmastyOrderAttributes extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * AmastyOrderAttributes constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->objectManager = $objectManager;
        $this->localeDate = $localeDate;
        $this->moduleList = $moduleList;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Amasty Order Attributes Export',
            'category' => 'Order',
            'description' => 'Export custom order attributes of Amasty Order Attributes extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Amasty_Orderattr',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['amasty_orderattributes']; // Write on "amasty_orderattributes" level

        if (!$this->fieldLoadingRequired('amasty_orderattributes')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            // Check module version
            $moduleInfo = $this->moduleList->getOne('Amasty_Orderattr');
            if (isset($moduleInfo['setup_version']) && version_compare($moduleInfo['setup_version'], '3.0.0', '>=')) {
                // Version 3.0.0+
                $entity = $this->objectManager->get('\Amasty\Orderattr\Model\Entity\EntityResolver')->getEntityByOrder($order);
                if (!$entity->isObjectNew()) {
                    $form = $this->createEntityForm($entity, $order);
                    $outputData = $form->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML);
                    foreach ($outputData as $attributeCode => $data) {
                        if (!empty($data)) {
                            $this->writeValue($attributeCode, $data);
                        }
                    }
                }
            } else {
                $orderAttributeValue = $this->objectManager->get('\Amasty\Orderattr\Model\Order\Attribute\Value');
                $orderAttributeValue->loadByOrderId($order->getId());
                $attributeMetadataDataProvider = $this->objectManager->get('\Amasty\Orderattr\Model\AttributeMetadataDataProvider');
                $attributeCollection = $attributeMetadataDataProvider->loadAttributesForEditFormByStoreId($order->getStoreId());
                if ($attributeCollection->getSize()) {
                    foreach ($attributeCollection as $attribute) {
                        $value = $this->prepareAttributeValue($orderAttributeValue, $attribute);
                        if ($attribute->getFrontendLabel() && $value) {
                            $this->writeValue($attribute->getAttributeCode(), str_replace('$', '\$', $value));
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }

        // Done
        return $returnArray;
    }

    protected function prepareAttributeValue($orderAttributeValue, $attribute)
    {
        $value = $orderAttributeValue->getData($attribute->getAttributeCode());
        switch ($attribute->getFrontendInput())
        {
            case 'select':
            case 'boolean':
            case 'radios':
                $value = $attribute->getSource()->getOptionText($value);
                break;
            case 'date':
                $value = $this->localeDate->formatDate($value);
                break;
            case 'datetime':
                $value = $this->localeDate->formatDateTime($value);
                break;
            case 'checkboxes':
                $value = explode(',', $value);
                $labels = [];
                foreach ($value as $item) {
                    $labels[] = $attribute->getSource()->getOptionText($item);
                }
                $value = implode(', ', $labels);
                break;
        }

        return $value;
    }

    protected function createEntityForm($entity, $order)
    {
        $formProcessor = $this->objectManager->get('\Amasty\Orderattr\Model\Value\Metadata\FormFactory')->create();
        $formProcessor->setFormCode('adminhtml_order_view')
            ->setEntity($entity)
            ->setStore($order->getStore());

        return $formProcessor;
    }
}