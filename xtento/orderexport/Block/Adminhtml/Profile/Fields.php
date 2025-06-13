<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2023-03-18T16:10:15+00:00
 * File:          Block/Adminhtml/Profile/Fields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Profile;

class Fields extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Xtento\OrderExport\Model\Output\Xml\Writer
     */
    protected $xmlWriter;

    /**
     * Fields constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Xtento\OrderExport\Model\Output\Xml\Writer $xmlWriter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Xtento\OrderExport\Model\Output\Xml\Writer $xmlWriter,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->xmlWriter = $xmlWriter;
        parent::__construct($context, $data);
    }

    public function getFieldJson()
    {
        $export = $this->objectManager->create('Xtento\OrderExport\Model\Export\Entity\\' . ucfirst($this->registry->registry('orderexport_profile')->getEntity()));
        $export->setShowEmptyFields(1);
        $export->setProfile($this->registry->registry('orderexport_profile'));
        $export->setCollectionFilters(
            [
                ['increment_id' => ['in' => explode(",", $this->getTestId())]]
            ]
        );
        $returnArray = $export->runExport();
        if (empty($returnArray)) {
            return false;
        }
        if (@class_exists('\Laminas\Json\Json')) {
            return \Laminas\Json\Json::encode($this->prepareJsonArray($returnArray));
        } else {
            return \Zend_Json::encode($this->prepareJsonArray($returnArray));
        }
    }

    /*
     * Convert Array into EXTJS TreePanel JSON
     */
    protected function prepareJsonArray($array, $parentKey = '')
    {
        static $depth = 0;
        $newArray = [];

        $depth++;
        if ($depth >= 250) {
            return '';
        }

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $key = $this->xmlWriter->handleSpecialParentKeys($key, $parentKey);
                $newArray[] = ['text' => $key, 'leaf' => false, 'expanded' => true, 'cls' => 'x-tree-noicon', 'children' => $this->prepareJsonArray($val, $key)];
            } else {
                if ($val == '') {
                    $val = __('NULL');
                }
                if (function_exists('mb_convert_encoding')) {
                    try {
                        $val = mb_convert_encoding($val, 'UTF-8', 'auto');
                    } catch (\Exception $e) {}
                }
                $newArray[] = ['text' => $key, 'leaf' => false, 'cls' => 'x-tree-noicon', 'children' => [['text' => $val, 'leaf' => true, 'cls' => 'x-tree-noicon']]];
            }
        }
        return $newArray;
    }

    public function getTestId()
    {
        return urldecode($this->getRequest()->getParam('test_id'));
    }

    public function getRegistry()
    {
        return $this->registry;
    }
}