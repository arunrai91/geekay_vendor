<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2022-05-06T19:57:17+00:00
 * File:          Model/Export/Data/Order/Payment/Transactions.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Order\Payment;

class Transactions extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    protected $transactions;

    /**
     * Transactions constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->transactions = $transactions;
    }


    public function getConfiguration()
    {
        return [
            'name' => 'Payment transaction information',
            'category' => 'Order Payment',
            'description' => 'Export payment transactions.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO],
        ];
    }

    /**
     * @param $entityType
     * @param $collectionItem
     *
     * @return array
     */
    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['payment_transactions']; // Write on payment_transactions level
        // Fetch fields to export
        $order = $collectionItem->getOrder();

        if (!$this->fieldLoadingRequired('payment_transactions')) {
            return $returnArray;
        }

        $transactionSearch = $this->transactions->create()->addOrderIdFilter($order->getId());
        $transactions = $transactionSearch->getItems();

        // Payment transactions
        if ($transactions) {
            foreach ($transactions as $transaction) {
                $this->writeArray = &$returnArray['payment_transactions'][];
                foreach ($transaction->getData() as $key => $value) {
                    if ($key === 'additional_information') {
                        continue;
                    }
                    $this->writeValue($key, $value);
                }

                $additionalInformation = $transaction->getData('additional_information');
                try {
                    if (is_string($additionalInformation)) {
                        if (version_compare($this->utilsHelper->getMagentoVersion(), '2.2', '>=')) {
                            $additionalInformation = json_decode($additionalInformation);
                        } else {
                            if (version_compare(phpversion(), '7.0.0', '>=')) {
                                $additionalInformation = unserialize($additionalInformation, ['allowed_classes' => false]);
                            } else {
                                $additionalInformation = unserialize($additionalInformation);
                            }
                        }
                    }
                } catch (\Exception $e) {}
                if ($additionalInformation && is_array($additionalInformation)) {
                    foreach ($additionalInformation as $key => $value) {
                        if (!is_array($value)) {
                            $this->writeValue($key, $value);
                        }
                    }
                }
            }
        }
        $this->writeArray = & $returnArray;
        // Done
        return $returnArray;
    }
}