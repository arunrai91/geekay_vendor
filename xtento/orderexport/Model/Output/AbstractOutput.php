<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2021-09-07T21:26:58+00:00
 * File:          Model/Output/AbstractOutput.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Output;

use Magento\Framework\Exception\LocalizedException;

abstract class AbstractOutput extends \Magento\Framework\Model\AbstractModel implements OutputInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Xtento\XtCore\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Xtento\OrderExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Xtento\OrderExport\Model\ResourceModel\Log\CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * AbstractOutput constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\OrderExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->localeDate = $localeDate;
        $this->dateHelper = $dateHelper;
        $this->profileFactory = $profileFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->logCollectionFactory = $logCollectionFactory;
    }

    public static $iteratingKeys = [
        'items',
        'transactions',
        'entries',
        'fields',
        'custom_options',
        'product_attributes',
        'product_options',
        'downloadable_links',
        'tracks',
        'order_status_history' => 'entry',
        'addresses' => 'address',
        'invoice_comments' => 'invoice_comment',
        'skus' => 'sku',
        'salesrules' => 'salesrule'
    ];

    protected function replaceFilenameVariables($filename, $exportArray)
    {
        $filename = str_replace("|", "-", $filename); // Remove the pipe character - it's not allowed in file names anyways and we use it to separate multiple files in the DB
        // Replace variables in filename
        $replaceableVariables = [
            '/%d%/' => $this->localeDate->date()->format('d'),
            '/%m%/' => $this->localeDate->date()->format('m'),
            '/%y%/' => $this->localeDate->date()->format('y'),
            '/%Y%/' => $this->localeDate->date()->format('Y'),
            '/%h%/' => $this->localeDate->date()->format('H'),
            '/%i%/' => $this->localeDate->date()->format('i'),
            '/%s%/' => $this->localeDate->date()->format('s'),
            '/%timestamp%/' => $this->localeDate->scopeTimeStamp(),
            '/%lastentityid%/' => $this->getVariableValue('last_entity_id', $exportArray, $filename, '%lastentityid%'),
            '/%orderid%/' => $this->getVariableValue('last_entity_id', $exportArray, $filename, '%orderid%'), // Legacy
            '/%lastincrementid%/' => $this->getVariableValue('last_increment_id', $exportArray, '/%lastincrementid%/' . $filename, '%lastincrementid%'), // Variable in filename so it's always retrieved
            '/%firstincrementid%/' => $this->getVariableValue('first_increment_id', $exportArray, $filename, '%firstincrementid%'),
            '/%lastorderincrementid%/' => $this->getVariableValue('last_order_increment_id', $exportArray, $filename, '%lastorderincrementid%'),
            '/%lastcustomeremail%/' => $this->getVariableValue('last_customer_email', $exportArray, '/%lastcustomeremail%/' . $filename, '%lastcustomeremail%'), // Variable in filename so it's always retrieved
            '/%realorderid%/' => $this->getVariableValue('last_increment_id', $exportArray, $filename, '%realorderid%'), // Legacy
            '/%ordercount%/' => $this->getVariableValue('collection_count', $exportArray, $filename, '%ordercount%'), // Legacy
            '/%collectioncount%/' => $this->getVariableValue('collection_count', $exportArray, $filename, '%collectioncount%'),
            '/%date_from_timestamp%/' => $this->getVariableValue('date_from_timestamp', $exportArray, $filename, '%date_from_timestamp%'),
            '/%date_to_timestamp%/' => $this->getVariableValue('date_to_timestamp', $exportArray, $filename, '%date_to_timestamp%'),
            '/%manual_export_date_from%/' => $this->getVariableValue('manual_export_date_from', $exportArray, $filename, '%manual_export_date_from%'),
            '/%manual_export_date_to%/' => $this->getVariableValue('manual_export_date_to', $exportArray, $filename, '%manual_export_date_to%'),
            '/%exportCountForObject%/' => $this->getVariableValue('export_count_for_object', $exportArray, $filename, '%exportCountForObject%'), // How often was this object exported before by this profile?
            '/%dailyExportCounter%/' => $this->getVariableValue('daily_export_counter', $exportArray, $filename, '%dailyExportCounter%'), // How many objects have been exported today by this profile?
            '/%profileExportCounter%/' => $this->getVariableValue('profile_export_counter', $exportArray, $filename, '%profileExportCounter%'), // How many objects have been exported by this profile? Basically an incrementing counter for each export
            '/%uuid%/' => uniqid(),
            '/%exportid%/' => $this->getVariableValue('export_id', $exportArray, $filename, '%exportid%'),
        ];

        // Ability to add custom variables to the filename using an event
        $transportObject = new \Magento\Framework\DataObject();
        $transportObject->setCustomVariables([]);
        $this->_eventManager->dispatch(
            'xtento_orderexport_replace_filename_variables_before',
            ['transport' => $transportObject, 'exportArray' => $exportArray]
        );
        $replaceableVariables = array_merge($replaceableVariables, $transportObject->getCustomVariables());

        // Register variables for other usage
        if (!empty($exportArray) || (empty($exportArray) && $this->_registry->registry('xtento_orderexport_export_variables') === null)) {
            $this->_registry->unregister('xtento_orderexport_export_variables');
            $this->_registry->register('xtento_orderexport_export_variables', $replaceableVariables);
        }

        $filename = preg_replace(array_keys($replaceableVariables), array_values($replaceableVariables), $filename);
        return $filename;
    }

    protected function getVariableValue($variable, $exportArray, $filename = false, $attributeVariableName = false)
    {
        if (!empty($filename) && !empty($attributeVariableName) && !stristr($filename, $attributeVariableName)) {
            // Variable not required in filename
            return '';
        }
        $arrayToWorkWith = $exportArray;
        if ($variable == 'export_id') {
            if ($this->_registry->registry('orderexport_log')) {
                return $this->_registry->registry('orderexport_log')->getId();
            } else {
                return 0;
            }
        }
        if ($variable == 'collection_count') {
            return count($arrayToWorkWith);
        }
        if ($variable == 'total_item_count') {
            $totalItemCount = 0;
            foreach ($arrayToWorkWith as $collectionObject) {
                if (isset($collectionObject['items'])) {
                    foreach ($collectionObject['items'] as $item) {
                        $totalItemCount++;
                    }
                }
            }
            return $totalItemCount;
        }
        if ($variable == 'last_entity_id') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['entity_id'])) {
                return $lastItem['entity_id'];
            }
        }
        if ($variable == 'first_increment_id') {
            $lastItem = array_shift($arrayToWorkWith);
            if (isset($lastItem['increment_id'])) {
                return $lastItem['increment_id'];
            } else {
                $id = isset($lastItem['entity_id']) ? $lastItem['entity_id'] : 'unset';
                return 'increment_not_set_' . $id;
            }
        }
        if ($variable == 'last_increment_id') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['increment_id'])) {
                return $lastItem['increment_id'];
            } else {
                $id = isset($lastItem['entity_id']) ? $lastItem['entity_id'] : 'unset';
                return 'increment_not_set_' . $id;
            }
        }
        if ($variable == 'last_order_increment_id') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['order']) && isset($lastItem['order']['increment_id'])) {
                return $lastItem['order']['increment_id'];
            } else if (isset($lastItem['increment_id'])) {
                return $lastItem['increment_id'];
            } else {
                return '';
            }
        }
        if ($variable == 'last_customer_email') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['customer_email'])) {
                return $lastItem['customer_email'];
            } elseif (isset($lastItem['order']) && isset($lastItem['order']['customer_email'])) {
                return $lastItem['order']['customer_email'];
            } else {
                $id = isset($lastItem['entity_id']) ? $lastItem['entity_id'] : 'unset';
                return 'no_customer_email_set' . $id;
            }
        }
        if ($variable == 'date_from_timestamp') {
            $firstObject = array_shift($arrayToWorkWith);
            return $this->dateHelper->convertDateToStoreTimestamp($firstObject['created_at']);
        }
        if ($variable == 'date_to_timestamp') {
            $lastObject = array_pop($arrayToWorkWith);
            return $this->dateHelper->convertDateToStoreTimestamp($lastObject['created_at']);
        }
        if ($variable == 'max_item_count') {
            $maxItemCount = 0;
            foreach ($arrayToWorkWith as $object) {
                if (!isset($object['items'])) {
                    continue;
                }
                $itemCount = count($object['items']);
                if ($maxItemCount === 0) {
                    $maxItemCount = $itemCount;
                }
                if ($itemCount > $maxItemCount) {
                    $maxItemCount = $itemCount;
                }
            }
            return $maxItemCount;
        }
        if ($variable == 'export_count_for_object') {
            $lastItem = array_pop($arrayToWorkWith);
            if (isset($lastItem['entity_id'])) {
                $exportEntity = false;
                $profileId = false;
                if ($this->_registry->registry('orderexport_log')) {
                    $profileId = $this->_registry->registry('orderexport_log')->getProfileId();
                    $profile = $this->profileFactory->create()->load($profileId);
                    $exportEntity = $profile->getEntity();
                }
                if ($this->_registry->registry('orderexport_profile')) {
                    $exportEntity = $this->_registry->registry('orderexport_profile')->getEntity();
                    $profileId = $this->_registry->registry('orderexport_profile')->getId();
                }
                if (!$exportEntity) {
                    return '';
                }
                $exportHistoryCollection = $this->historyCollectionFactory->create();
                $exportHistoryCollection->addFieldToFilter('entity', $exportEntity);
                $exportHistoryCollection->addFieldToFilter('entity_id', $lastItem['entity_id']);
                $exportHistoryCollection->addFieldToFilter('profile_id', $profileId);
                return $exportHistoryCollection->getSize() + 1;
            }
        }
        if ($variable == 'daily_export_counter' || $variable == 'profile_export_counter') {
            $exportEntity = false;
            $profileId = false;
            if ($this->_registry->registry('orderexport_log')) {
                $profileId = $this->_registry->registry('orderexport_log')->getProfileId();
                $profile = $this->profileFactory->create()->load($profileId);
                $exportEntity = $profile->getEntity();
            }
            if ($this->_registry->registry('orderexport_profile')) {
                $exportEntity = $this->_registry->registry('orderexport_profile')->getEntity();
                $profileId = $this->_registry->registry('orderexport_profile')->getId();
            }
            if (!$exportEntity) {
                return '';
            }
            $exportLogCollection = $this->logCollectionFactory->create();
            #$exportHistoryCollection->addFieldToFilter('entity', $exportEntity);
            if ($variable == 'daily_export_counter') {
                $exportLogCollection->getSelect()->where('DATE(created_at) = DATE(NOW())');
            }
            $exportLogCollection->addFieldToFilter('main_table.profile_id', $profileId);
            $exportLogCollection->getSelect()->where('records_exported > 0');
            return $exportLogCollection->getSize();
        }
        // GUID
        if ($variable == 'guid') {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                random_int(0, 0xffff), random_int(0, 0xffff),
                // 16 bits for "time_mid"
                random_int(0, 0xffff),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                random_int(0, 0x0fff) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                random_int(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
            );
        }
        return '';
    }

    protected function throwXmlException($message)
    {
        $message .= "\n";
        foreach (libxml_get_errors() as $error) {
            // XML error codes: http://www.xmlsoft.org/html/libxml-xmlerror.html
            $message .= "\tLine " . $error->line . " (Error Code: ".$error->code."): " . $error->message;
            if (strpos($error->message, "\n") === FALSE) {
                $message .= "\n";
            }
        }
        libxml_clear_errors();
        throw new LocalizedException(__($message));
    }

    protected function changeEncoding($input, $encoding, $charsetLocale = '')
    {
        if (!empty($charsetLocale)) {
            // Set locale based on XSL Template "locale" attribute
            $oldLocale = setlocale(LC_CTYPE, "0"); // Get current locale
            try {
                setlocale(LC_CTYPE, $charsetLocale);
            } catch (\Exception $e) {}
        }
        $output = $input;
        if (!empty($encoding) && function_exists('iconv')) {
            $output = false;
            try {
                $output = iconv("UTF-8", $encoding, $input);
            } catch (\Exception $e) {}
            if (!$output && !empty($input)) {
                // Conversion failed, try as UTF-8 re-encoded
                try {
                    $output = iconv("UTF-8", $encoding, utf8_encode(utf8_decode($input)));
                } catch (\Exception $e) {}
                if (!$output && !empty($input)) {
                    if (!empty($charsetLocale)) {
                        // Reset locale
                        setlocale(LC_CTYPE, $oldLocale);
                    }
                    $this->throwXmlException(__("While trying to convert your export data into the requested encoding '%1', the PHP iconv() function failed. You either forgot to add //IGNORE to the encoding, or you are affected by this bug: https://bugs.php.net/bug.php?id=48147", $encoding));
                }
            }
        }
        if (!empty($charsetLocale)) {
            // Reset locale
            setlocale(LC_CTYPE, $oldLocale);
        }
        return $output;
    }
}