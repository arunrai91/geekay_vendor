<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2016-03-02T18:20:46+00:00
 * File:          Model/Export/Data/Shared/SalesRule.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Shared;

class SalesRule extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * SalesRule constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->ruleFactory = $ruleFactory;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Sales Rules', // Thanks to Thomas Hägi!
            'category' => 'Order',
            'description' => 'Export sales rules used by the order',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO],
            'third_party' => false,
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['salesrules'];

        // Get order
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('salesrules')) {
            return $returnArray;
        }

        // Get applied rules
        $ruleIds = $order->getAppliedRuleIds();
        if ($ruleIds) {
            $ruleIds = explode(',', $ruleIds);
            foreach ($ruleIds as $ruleId) {
                // Load rule object
                $rule = $this->ruleFactory->create()->load($ruleId);
                if ($rule && $rule->getId()) {
                    // Export rule
                    $this->writeArray = & $returnArray['salesrules'][];
                    foreach ($rule->getData() as $key => $value) {
                        $this->writeValue($key, $value);
                    }
                }
            }
        }

        // Done
        return $returnArray;
    }
}