<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2022-04-15T20:11:52+00:00
 * File:          Model/Export/Data/Order/Payment/Cc.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Order\Payment;

class Cc extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Cc constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->encryptor = $encryptor;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Credit Card Information',
            'category' => 'Order Payment',
            'description' => 'Export decrypted credit card information for payment methods saving the CC# into the cc_number_enc field.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO]
        ];
    }

    // @codingStandardsIgnoreStart
    public function getExportData($entityType, $collectionItem)
    {
        // @codingStandardsIgnoreEnd
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['payment']; // Write into payment fields
        // Fetch fields to export
        $payment = $collectionItem->getOrder()->getPayment();
        if ($payment) {
            try {
                $this->writeValue('cc_number_dec', preg_replace("/[^0-9\-]/", "", $this->encryptor->decrypt($payment->getCcNumberEnc())));
            } catch (\Exception $e) {
                $this->writeValue('cc_number_dec', 'DECRYPTION_FAILED_WRONG_KEY');
            }
            try {
                $this->writeValue('cc_cvv2_dec', preg_replace("/[^0-9\-]/", "", $this->encryptor->decrypt($payment->getCcCidEnc())));
            } catch (\Exception $e) {
                $this->writeValue('cc_cvv2_dec', 'DECRYPTION_FAILED_WRONG_KEY');
            }
            $this->writeValue('cc_cvv2', preg_replace("/[^0-9\-]/", "", (string)$payment->getCcCid()));
        }
        // Done
        return $returnArray;
    }
}