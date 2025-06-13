<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2021-08-05T15:02:59+00:00
 * File:          Model/Export/Data/Custom/Order/AmastyExtraFee.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class AmastyExtraFee extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * AmastyExtraFee constructor.
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
            'name' => 'Amasty Extra Fee Data Export',
            'category' => 'Order',
            'description' => 'Export fee information stored against order by Amasty fee module',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Amasty_Extrafee',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = &$returnArray['amasty_extrafee']; // Write on "amasty_extrafee" level

        if (!$this->fieldLoadingRequired('amasty_extrafee')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            $feesQuoteCollection = $this->objectManager->get('\Amasty\Extrafee\Model\ResourceModel\ExtrafeeQuote\CollectionFactory')->create()
                ->addFieldToFilter('option_id', ['neq' => '0'])
                ->addFieldToFilter('quote_id', $order->getQuoteId());

            foreach ($feesQuoteCollection as $feeOption) {
                $this->writeArray = &$returnArray['amasty_extrafee'][];
                foreach ($feeOption->getData() as $key => $value) {
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