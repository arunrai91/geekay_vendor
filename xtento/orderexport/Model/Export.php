<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2023-03-15T08:29:35+00:00
 * File:          Model/Export.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model;

use Magento\Framework\Exception\LocalizedException;
use Xtento\XtCore\Helper\Utils;

class Export extends \Magento\Framework\Model\AbstractModel
{
    // Export entities
    const ENTITY_CUSTOMER = 'customer';
    const ENTITY_ORDER = 'order';
    const ENTITY_INVOICE = 'invoice';
    const ENTITY_SHIPMENT = 'shipment';
    const ENTITY_CREDITMEMO = 'creditmemo';
    const ENTITY_QUOTE = 'quote'; // Experimental
    const ENTITY_AWRMA = 'awrma'; // aheadWorks RMA, not yet implemented
    const ENTITY_BOOSTRMA = 'boostrma'; // BoostMyShop Product Return / RMA, not yet implemented
    const ENTITY_EERMA = 'eerma'; // Magento Commerce (EE) RMA

    // Export types
    const EXPORT_TYPE_TEST = 0; // Test Export
    const EXPORT_TYPE_GRID = 1; // Grid Export
    const EXPORT_TYPE_MANUAL = 2; // From "Manual Export" screen
    const EXPORT_TYPE_CRONJOB = 3; // Cronjob Export
    const EXPORT_TYPE_EVENT = 4; // Export after event

    /**
     * @var \Xtento\XtCore\Helper\Server
     */
    protected $serverHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderStatuses;

    /**
     * @var \Xtento\OrderExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var ExportFactory
     */
    protected $exportFactory;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Xtento\OrderExport\Logger\Logger
     */
    protected $xtentoLogger;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Utils
     */
    protected $utilsHelper;

    /**
     * Export constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Sales\Model\Order\Config $orderStatuses
     * @param ProfileFactory $profileFactory
     * @param ExportFactory $exportFactory
     * @param LogFactory $logFactory
     * @param HistoryFactory $historyFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Xtento\OrderExport\Logger\Logger $xtentoLogger
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Utils $utilsHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Config $orderStatuses,
        ProfileFactory $profileFactory,
        ExportFactory $exportFactory,
        LogFactory $logFactory,
        HistoryFactory $historyFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Xtento\OrderExport\Logger\Logger $xtentoLogger,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Utils $utilsHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->request = $request;
        $this->serverHelper = $serverHelper;
        $this->moduleHelper = $moduleHelper;
        $this->objectManager = $objectManager;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->orderStatuses = $orderStatuses;
        $this->profileFactory = $profileFactory;
        $this->exportFactory = $exportFactory;
        $this->logFactory = $logFactory;
        $this->historyFactory = $historyFactory;
        $this->orderFactory = $orderFactory;
        $this->transactionFactory = $transactionFactory;
        $this->xtentoLogger = $xtentoLogger;
        $this->shipmentFactory = $shipmentFactory;
        $this->scopeConfig = $scopeConfig;
        $this->emulation = $emulation;
        $this->utilsHelper = $utilsHelper;
    }

    /**
     * Get export entities
     *
     * @return array
     */
    public function getEntities()
    {
        $values = [];
        $values[self::ENTITY_ORDER] = __('Orders');
        $values[self::ENTITY_INVOICE] = __('Invoices');
        $values[self::ENTITY_SHIPMENT] = __('Shipments');
        $values[self::ENTITY_CREDITMEMO] = __('Credit Memos');
        $values[self::ENTITY_CUSTOMER] = __('Customers');
        if ($this->getExperimentalFeatureSupport()) {
            $values[self::ENTITY_QUOTE] = __('Quotes');
        }
        /*if (Mage::helper('xtcore/utils')->isExtensionInstalled('AW_Rma')) {
            $values[self::ENTITY_AWRMA] = __('aheadWorks RMA');
        }
        if (Mage::helper('xtcore/utils')->isExtensionInstalled('MDN_ProductReturn')) {
            $values[self::ENTITY_BOOSTRMA] = __('BoostMyShop RMA');
        }*/
        if ($this->utilsHelper->isMagentoEnterprise() && $this->utilsHelper->isExtensionInstalled('Magento_Rma')) {
            $values[self::ENTITY_EERMA] = __('Magento Commerce RMA');
        }
        return $values;
    }

    /**
     * Get export types
     *
     * @return array
     */
    public function getExportTypes()
    {
        $values = [];
        $values[self::EXPORT_TYPE_TEST] = __('Test Export');
        $values[self::EXPORT_TYPE_MANUAL] = __('Manual Export');
        $values[self::EXPORT_TYPE_GRID] = __('Grid Export');
        $values[self::EXPORT_TYPE_CRONJOB] = __('Cronjob Export');
        $values[self::EXPORT_TYPE_EVENT] = __('Event Export');
        return $values;
    }


    /**
     * Validate test XSL Template function
     *
     * @param bool $exportId
     * @return array|\Magento\Framework\Phrase
     * @throws LocalizedException
     */
    public function testExport($exportId = false)
    {
        if (empty($exportId)) {
            return __('No test ID to export specified.');
        }
        $this->setExportType(self::EXPORT_TYPE_TEST);
        $this->_registry->register('is_test_orderexport', true, true);
        $filters[] = ['increment_id' => ['in' => explode(",", $exportId)]];
        $exportedFiles = $this->runExport($filters);
        return $exportedFiles;
    }

    /**
     * Export from a grid
     *
     * @param $exportIds
     * @return array
     * @throws LocalizedException
     */
    public function gridExport($exportIds)
    {
        if (empty($exportIds)) {
            throw new LocalizedException(
                __('No %1s to export specified.', $this->getProfile()->getEntity())
            );
        }
        $this->checkStatus();
        $this->setExportType(self::EXPORT_TYPE_GRID);
        $this->beforeExport();
        $filters[] = ['entity_id' => ['in' => $exportIds]];
        $generatedFiles = $this->runExport($filters);
        if ($this->getProfile()->getSaveFilesManualExport()) {
            $this->saveFiles();
        }
        $this->afterExport();
        return $generatedFiles;
    }

    /**
     * Manual export from "Manual Export" screen
     *
     * @param $filters
     * @return array
     * @throws LocalizedException
     */
    public function manualExport($filters)
    {
        $this->checkStatus();
        $this->setExportType(self::EXPORT_TYPE_MANUAL);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters);
        if ($this->getProfile()->getSaveFilesManualExport()) {
            $this->saveFiles();
        }
        $this->afterExport();
        return $generatedFiles;
    }

    /**
     * Event based export
     *
     * @param $filters
     * @param bool $forcedCollectionItem
     * @return bool
     * @throws LocalizedException
     */
    public function eventExport($filters, $forcedCollectionItem = false)
    {
        $this->setExportType(self::EXPORT_TYPE_EVENT);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters, $forcedCollectionItem);
        if (empty($generatedFiles) && ($this->getLogEntry()->getResult() === NULL || $this->getLogEntry()->getResult() === Log::RESULT_SUCCESSFUL)) {
            $this->getLogEntry()->delete();
            return false;
        }
        $this->saveFiles();
        $this->afterExport();
        return true;
    }


    /**
     * Cronjob export
     *
     * @param $filters
     * @return bool
     * @throws LocalizedException
     */
    public function cronExport($filters)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return true;
        }

        $this->setExportType(self::EXPORT_TYPE_CRONJOB);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters);
        if (empty($generatedFiles) && ($this->getLogEntry()->getResult() === NULL || $this->getLogEntry()->getResult() === Log::RESULT_SUCCESSFUL)) {
            $this->getLogEntry()->delete();
            return false;
        }
        $this->saveFiles();
        $this->afterExport();
        return true;
    }

    /**
     * Merged export - a special export type where multiple profiles are exported at the same time
     *
     * @param $filters
     * @return array
     * @throws LocalizedException
     */
    public function mergedExport($filters)
    {
        $this->setExportType(self::EXPORT_TYPE_CRONJOB);
        $this->beforeExport();
        $generatedFiles = $this->runExport($filters);
        $this->getLogEntry()->addResultMessage(
            __('Exported in merged export mode.')
        );
        $this->saveFiles();
        $this->afterExport();
        return $generatedFiles;
    }

    /**
     * Called by all export routines, initiates the export
     *
     * @param $filters
     * @param bool $forcedCollectionItem
     * @return array
     * @throws LocalizedException
     */
    protected function runExport($filters, $forcedCollectionItem = false)
    {
        try {
            if (function_exists('set_time_limit')) {
                try {
                    set_time_limit(0);
                } catch (\Exception $e) {}
            }
            $this->serverHelper->increaseMemoryLimit('2048M');
            if (!$this->getProfile()) {
                throw new LocalizedException(__('No profile to export specified.'));
            }
            if (preg_match('/\|merge\:/', $this->getProfile()->getXslTemplate())) {
                // Merge multiple profiles. Format: filename|merge:1,3,4,5 (<- profile ids)
                $generatedFiles = [];
                $mergeConfig = $this->getProfile()->getXslTemplate();
                $mergeResultFilename = explode("|", $mergeConfig);
                $mergeResultFilename = array_shift($mergeResultFilename);
                $mergeConfig = str_replace($mergeResultFilename."|merge:", '', $mergeConfig);
                $profileIds = explode(",", $mergeConfig);
                $recordsExported = 0;
                $generatedFile = "";
                foreach ($profileIds as $profileId) {
                    $profile = $this->profileFactory->create()->load($profileId);
                    if ($profile->getId()) {
                        $exportModel = $this->exportFactory->create()->setProfile($profile);
                        $exportedFiles = $exportModel->mergedExport($filters);
                        foreach ($exportedFiles as $exportedFilename => $exportedData) {
                            $generatedFile .= $exportedData;
                            $recordsExported += $this->_registry->registry('orderexport_log')->getRecordsExported();
                        }
                    }
                }
                if ($this->_registry->registry('xtento_orderexport_export_variables') !== null) {
                    $replaceableVariables = $this->_registry->registry('xtento_orderexport_export_variables');
                    $generatedFilename = preg_replace(
                        array_keys($replaceableVariables),
                        array_values($replaceableVariables),
                        $mergeResultFilename
                    );
                    $generatedFiles[$generatedFilename] = $generatedFile;
                } else {
                    $generatedFiles[$mergeResultFilename] = $generatedFile;
                }
                // Re-register profile, log
                $this->_registry->unregister('orderexport_log');
                $this->_registry->unregister('orderexport_profile');
                $this->_registry->register('orderexport_log', $this->getLogEntry());
                $this->_registry->register('orderexport_profile', $this->getProfile());
                $this->getLogEntry()->setRecordsExported($recordsExported);
            } else {
                // Normal export, no merged export
                $returnArray = $this->exportObjects($filters, $forcedCollectionItem);
                if (empty($returnArray) && !$this->getProfile()->getExportEmptyFiles()) {
                    throw new LocalizedException(
                        __('0 %1s have been exported.', $this->getProfile()->getEntity())
                    );
                }
                $this->setReturnArrayWithObjects($returnArray);
                // Get output type
                if ($this->getProfile()->getOutputType() == 'csv') {
                    $type = 'csv';
                } else {
                    if ($this->getProfile()->getOutputType() == 'xml') {
                        $type = 'xml';
                    } else {
                        $type = 'xsl';
                    }
                }
                // Convert data
                if ($this->getProfile()->getExportOneFilePerObject()) {
                    // Create one file per exported object
                    $generatedFiles = [];
                    foreach ($this->getReturnArrayWithObjects() as $returnObject) {
                        $generatedFiles += $this->objectManager->create(
                            '\Xtento\OrderExport\Model\Output\\' . ucfirst($type)
                        )->setProfile($this->getProfile())->convertData([$returnObject]);
                    }
                } else {
                    // Create just one file for all exported objects
                    $generatedFiles = $this->objectManager->create(
                        '\Xtento\OrderExport\Model\Output\\' . ucfirst($type)
                    )->setProfile($this->getProfile())->convertData($this->getReturnArrayWithObjects());
                }
            }
            // Check for empty files
            if (!$this->getProfile()->getExportEmptyFiles()) {
                foreach ($generatedFiles as $filename => $data) {
                    if (strlen((string)$data) === 0) {
                        unset($generatedFiles[$filename]);
                    }
                }
            }
            // Set generated files
            $this->setGeneratedFiles($generatedFiles);
            if (is_array($this->getReturnArrayWithObjects()) && $this->getLogEntry()) {
                $this->getLogEntry()->setRecordsExported(count($this->getReturnArrayWithObjects()));
            }
            return $generatedFiles;
        } catch (\Exception $e) {
            if ($this->getLogEntry()) {
                $result = Log::RESULT_FAILED;
                if (preg_match('/have been exported/', $e->getMessage())) {
                    if ($this->getExportType() == self::EXPORT_TYPE_MANUAL || $this->getExportType(
                        ) == self::EXPORT_TYPE_GRID
                    ) {
                        $result = Log::RESULT_WARNING;
                    } else {
                        return [];
                    }
                }
                $this->getLogEntry()->setResult($result);
                $this->getLogEntry()->addResultMessage($e->getMessage());
                $this->afterExport();
            }
            if ($this->getExportType() == self::EXPORT_TYPE_MANUAL || $this->getExportType(
                ) == self::EXPORT_TYPE_GRID || $this->getExportType() == self::EXPORT_TYPE_TEST
            ) {
                throw new LocalizedException(__($e->getMessage()));
            }
            return [];
        }
    }

    /**
     * Export objects
     *
     * @param $filters
     * @param bool $forcedCollectionItem
     * @return mixed
     */
    protected function exportObjects($filters, $forcedCollectionItem = false)
    {
        $export = $this->objectManager->create(
            '\Xtento\OrderExport\Model\Export\Entity\\' . ucfirst($this->getProfile()->getEntity())
        );
        $export->setExportType($this->getExportType());
        $collection = $export->setCollectionFilters($filters);
        if ($this->getProfile()->getExportFilterNewOnly() &&
            ($this->getExportType() == self::EXPORT_TYPE_CRONJOB || $this->getExportType() == self::EXPORT_TYPE_EVENT)
        ) {
            $this->addExportOnlyNewFilter($collection);
            $export->addExportOnlyNewFilter();
        }
        if ($this->getExportFilterNewOnly() && ($this->getExportType() == self::EXPORT_TYPE_MANUAL
                /* || $this->getExportType() == self::EXPORT_TYPE_GRID*/)
        ) {
            $this->addExportOnlyNewFilter($collection);
        }
        #var_dump($filters);
        #echo $collection->getSelect();
        #echo $collection->count(); die();
        $export->setProfile($this->getProfile());
        return $export->runExport($forcedCollectionItem);
    }

    protected function addExportOnlyNewFilter($collection)
    {
        $joinTable = ($this->getProfile()->getEntity() == self::ENTITY_CUSTOMER) ? 'e' : 'main_table';
        $checkField = 'entity_id';
        /*if ($this->getProfile()->getEntity() == self::ENTITY_AWRMA) {
            $checkField = 'id';
        } else if ($this->getProfile()->getEntity() == self::ENTITY_BOOSTRMA) {
            $checkField = 'rma_id';
        }*/
        // Filter and hide objects that have been exported previously
        $collection->getSelect()->joinLeft(
            ['export_history' => $collection->getTable('xtento_orderexport_profile_history')],
            $joinTable . '.' . $checkField . ' = export_history.entity_id and ' . $collection->getConnection(
            )->quoteInto(
                'export_history.entity = ?',
                $this->getProfile()->getEntity()
            ) . ' and ' . $collection->getConnection()->quoteInto(
                'export_history.profile_id = ?',
                $this->getProfile()->getId()
            ),
            []
        );
        $collection->getSelect()->where('export_history.entity_id IS NULL');
        #echo $collection->getSelect(); die();
    }

    /*
     * Save files on their destinations
     */
    protected function saveFiles()
    {
        $this->_eventManager->dispatch('xtento_orderexport_before_save_files',
            [
                'export' => $this
            ]
        );
        try {
            foreach ($this->getProfile()->getDestinations() as $destination) {
                try {
                    $savedFiles = $destination->saveFiles($this->getGeneratedFiles());
                    if (is_array($this->getFiles()) && is_array($savedFiles)) {
                        $this->setFiles(array_merge($this->getFiles(), $savedFiles));
                    } else {
                        $this->setFiles($savedFiles);
                    }
                } catch (\Exception $e) {
                    $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                    $this->getLogEntry()->addResultMessage($e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->getLogEntry()->setResult(Log::RESULT_FAILED);
            $this->getLogEntry()->addResultMessage($e->getMessage());
            if ($this->getExportType() == self::EXPORT_TYPE_MANUAL) {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
    }

    /**
     * Called before every export
     */
    protected function beforeExport()
    {
        $this->setBeginTime(time());
        #$memBefore = memory_get_usage();
        #$timeBefore = time();
        #echo "Before export: " . $memBefore . " bytes / Time: " . $timeBefore . "<br>";
        $logEntry = $this->logFactory->create();
        $logEntry->setCreatedAt(time());
        $logEntry->setProfileId($this->getProfile()->getId());
        $logEntry->setDestinationIds($this->getProfile()->getDestinationIds());
        $logEntry->setExportType($this->getExportType());
        $logEntry->setRecordsExported(0);
        $logEntry->setResultMessage(__('Export started...'));
        $logEntry->save();
        $this->setLogEntry($logEntry);
        $this->_registry->unregister('orderexport_log');
        $this->_registry->unregister('orderexport_profile');
        $this->_registry->register('orderexport_log', $logEntry);
        $this->_registry->register('orderexport_profile', $this->getProfile());
        \Xtento\OrderExport\Helper\GracefulDie::enable();
    }

    /**
     * Called after every export
     */
    protected function afterExport()
    {
        if ($this->getLogEntry()->getResult() !== Log::RESULT_FAILED) {
            $this->_registry->register('do_not_process_event_exports', true, true);
            $this->invoiceShipOrder();
            $this->adjustOrderStatus();
            $this->cancelOrder();
            #$this->createExportHistoryEntries();
            if ($this->getProfile()->getExportFilterNewOnly() || $this->getExportFilterNewOnly()) {
                $this->createExportHistoryEntries();
            }
            $this->_registry->unregister('do_not_process_event_exports');
        }
        \Xtento\OrderExport\Helper\GracefulDie::disable();
        $this->saveLog();
        $this->_registry->unregister('orderexport_profile');
        #echo "After export: " . memory_get_usage() . " (Difference: " . round((memory_get_usage() - $memBefore) / 1024 / 1024, 2) . " MB, " . (time() - $timeBefore) . " Secs) - Count: " . (count($exportIds)) . " -  Per entry: " . round(((memory_get_usage() - $memBefore) / 1024 / 1024) / (count($exportIds)), 2) . "<br>";
        // Dispatch event after export
        $this->_eventManager->dispatch('xtento_orderexport_export_after',
            [
                'profile' => $this->getProfile(),
                'log' => $this->getLogEntry(),
                'objects' => $this->getReturnArrayWithObjects(),
                'files' => $this->getGeneratedFiles(),
                'export' => $this
            ]
        );
        return $this;
    }

    /**
     * Create export history entries after exporting, if enabled for profile. Important for "Export only new ..." feature
     */
    protected function createExportHistoryEntries()
    {
        if ($this->getReturnArrayWithObjects()) {
            // Save exported object ids in the export history
            foreach ($this->getReturnArrayWithObjects() as $object) {
                $historyEntry = $this->historyFactory->create();
                $historyEntry->setProfileId($this->getProfile()->getId());
                $historyEntry->setLogId($this->getLogEntry()->getId());
                $historyEntry->setEntity($this->getProfile()->getEntity());
                $historyEntry->setEntityId($object['entity_id']);
                $historyEntry->setExportedAt(time());
                $historyEntry->save();
            }
        }
    }

    /**
     * Function to adjust order status
     *
     * @throws LocalizedException
     */
    protected function adjustOrderStatus()
    {
        $statusUpdated = false;
        if ($this->getProfile()->getEntity() == self::ENTITY_ORDER) {
            if (($this->getProfile()->getExportActionChangeStatus() !== '' || $this->getForceChangeStatus() !== null) &&
                ($this->getExportType() == self::EXPORT_TYPE_MANUAL || $this->getExportType() == self::EXPORT_TYPE_GRID)
            ) {
                if ($this->getForceChangeStatus() !== 'no_change') {
                    if ($this->getForceChangeStatus() !== null) {
                        $this->changeOrderStatus($this->getForceChangeStatus());
                    } else {
                        $this->changeOrderStatus($this->getProfile()->getExportActionChangeStatus());
                    }
                    $statusUpdated = true;
                }
            }
            if ($this->getProfile()->getExportActionChangeStatus() !== '' &&
                ($this->getExportType() == self::EXPORT_TYPE_EVENT
                    || $this->getExportType() == self::EXPORT_TYPE_CRONJOB)
            ) {
                $this->changeOrderStatus($this->getProfile()->getExportActionChangeStatus());
                $statusUpdated = true;
            }
        }
        if (!$statusUpdated && $this->getProfile()->getExportActionAddComment() != '') {
            $this->addStatusHistoryComment();
        }
    }

    /**
     * Function to invoice/ship order
     */
    protected function invoiceShipOrder()
    {
        if ($this->getProfile()->getEntity() == self::ENTITY_ORDER) {
            $returnArray = $this->getReturnArrayWithObjects();
            if (empty($returnArray)) {
                return;
            }
            if (!$this->getProfile()->getExportActionInvoiceOrder() && !$this->getProfile()->getExportActionShipOrder()
                &&
                !$this->getProfile()->getExportActionInvoiceNotify() && !$this->getProfile()->getExportActionShipNotify()
            ) {
                return;
            }
            $doNotifyInvoice = $this->getProfile()->getExportActionInvoiceNotify();
            $doNotifyShipment = $this->getProfile()->getExportActionShipNotify();
            foreach ($returnArray as $object) {
                try {
                    /** @var \Magento\Sales\Model\Order $order */
                    $order = $this->orderFactory->create()->load($object['entity_id']);
                    if (!$order->getId()) {
                        continue;
                    }
                    $this->emulation->startEnvironmentEmulation($order->getStoreId(), \Magento\Framework\App\Area::AREA_FRONTEND, true);
                    // Invoice order
                    if ($this->getProfile()->getExportActionInvoiceOrder() && $order->canInvoice()) {
                        /** @var $invoice \Magento\Sales\Model\Order\Invoice */
                        $invoice = $order->prepareInvoice();
                        if ($invoice->canCapture()) {
                            // Capture order online
                            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                        } else {
                            // Set invoice status to Paid
                            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                        }
                        $invoice->register();
                        $invoice->setCustomerNoteNotify($doNotifyInvoice);

                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->transactionFactory->create()
                            ->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();

                        if ($doNotifyInvoice) {
                            $this->invoiceSender->send($invoice);
                        }
                        unset($invoice);
                    }
                    // Just (re-)send the invoice email even though the order has been invoiced already
                    /*if ($doNotifyInvoice && !$order->canInvoice()) {
                        $invoices = $order->getInvoiceCollection();
                        $lastInvoice = $invoices->getFirstItem();
                        if ($lastInvoice->getId()) {
                            $lastInvoice->setCustomerNoteNotify(true);
                            $this->invoiceSender->send($lastInvoice);
                            $lastInvoice->save();
                        }
                    }*/
                    // Ship order
                    if ($this->getProfile()->getExportActionShipOrder() && $order->canShip()) {
                        $items = [];
                        foreach ($order->getAllItems() as $orderItem) {
                            $items[$orderItem->getId()] = $orderItem->getQtyToShip();
                        }
                        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                        $shipment = $this->shipmentFactory->create($order, $items);
                        $this->setMsiSource($shipment);
                        $shipment->register();
                        $shipment->setCustomerNoteNotify($doNotifyShipment);
                        $shipment->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->transactionFactory->create()
                            ->addObject($shipment)->addObject($shipment->getOrder());
                        $transactionSave->save();

                        if ($doNotifyShipment) {
                            $this->shipmentSender->send($shipment);
                        }
                        unset($shipment);
                    }
                    // Just (re-)send the shipment email even though the order has been shipped already
                    /*if ($doNotifyShipment && !$order->canShip()) {
                        $shipments = $order->getShipmentsCollection();
                        $lastShipment = $shipments->getFirstItem();
                        if ($lastShipment->getId()) {
                            $lastShipment->setCustomerNoteNotify(true);
                            $this->shipmentSender->send($lastShipment);
                            $lastShipment->save();
                        }
                    }*/
                    $this->emulation->stopEnvironmentEmulation();
                } catch (\Exception $e) {
                    $this->xtentoLogger->warning(
                        'Exception catched while invoicing/shipping order# ' . $object['increment_id'] . ': ' . $e->getMessage(
                        )
                    );
                    $this->emulation->stopEnvironmentEmulation();
                    continue;
                }
            }
        }
    }

    /**
     * Set Magento 2.3 MSI source. Must use ObjectManager as otherwise code would not be compatible with Magento <2.3
     *
     * @param $shipment
     * @param bool $sourceCode
     *
     * @return $this
     */
    protected function setMsiSource($shipment, $sourceCode = false)
    {
        if (version_compare($this->utilsHelper->getMagentoVersion(), '2.3', '<') || !$this->utilsHelper->isExtensionInstalled('Magento_Inventory') || !$this->utilsHelper->isExtensionInstalled('Magento_InventorySalesApi') || !$this->utilsHelper->isExtensionInstalled('Magento_InventoryShippingAdminUi')) {
            return $this;
        }

        $sourceCode = false;
        $shipmentExtension = $shipment->getExtensionAttributes();
        if ($shipmentExtension && $shipmentExtension->getSourceCode()) {
            // Already set by MSI
            $sourceCode = $shipmentExtension->getSourceCode();
        }
        if (empty($shipmentExtension)) {
            $shipmentExtension = $this->objectManager->create('Magento\Sales\Api\Data\ShipmentExtensionFactory')->create();
        }
        // Determine source code
        $order = $shipment->getOrder();
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual()
                || $orderItem->getLockedDoShip()
                || $orderItem->getHasChildren()) {
                continue;
            }

            $item = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
            $qty = $item->getSimpleQtyToShip();
            $sku = $this->objectManager->create('Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface')->execute($item);
            $sources = $this->objectManager->create('Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty')->execute($order->getId(), $sku, $qty);
            if (isset($sources[0]) && isset($sources[0]['sourceCode'])) {
                $sourceCode = $sources[0]['sourceCode'];
                break;
            }
        }
        if ($sourceCode === false) {
            // Get default source
            $sourceCode = $this->objectManager->create('Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface')->getCode();
        }
        $shipmentExtension->setSourceCode($sourceCode);
        $shipment->setExtensionAttributes($shipmentExtension);
    }

    /**
     * Change order status function
     *
     * @param $newStatus
     * @throws LocalizedException
     */
    protected function changeOrderStatus($newStatus)
    {
        if ($newStatus == '') {
            throw new LocalizedException(__('No status to set for orders specified.'));
        }
        $returnArray = $this->getReturnArrayWithObjects();
        if (empty($returnArray)) {
            return;
        }
        foreach ($returnArray as $object) {
            try {
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderFactory->create()->load($object['entity_id']);
                if ($order->getId()) {
                    if ($order->getStatus() !== $newStatus) {
                        $commentAdded = $this->setOrderState($order, $newStatus);
                        if (!$commentAdded && $this->getProfile()->getExportActionAddComment() != '') {
                            $order->addStatusHistoryComment(
                                $this->getProfile()->getExportActionAddComment(),
                                false
                            )->setIsCustomerNotified(0);
                        }
                        // Compatibility fix for Amasty_OrderStatus
                        /*$statusModel = $this->_registry->registry('amorderstatus_history_status');
                        if (($statusModel && $statusModel->getNotifyByEmail()) || $this->_registry->registry('advancedorderstatus_notifications')) {
                            $order->sendOrderUpdateEmail();
                        }*/
                        // End
                        $order->save();
                    } else {
                        // Order status was changed already by invoice/ship action for example and thus only the commend must be added
                        if ($this->getProfile()->getExportActionAddComment() != '') {
                            $order->addStatusHistoryComment(
                                $this->getProfile()->getExportActionAddComment(),
                                false
                            )->setIsCustomerNotified(0);
                            $order->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->xtentoLogger->warning(
                    'Exception catched while changing order status for order# ' . $object['increment_id'] . ': ' . $e->getMessage(
                    )
                );
                continue;
            }
        }
    }

    /**
     * This function is only called if no "Change order status" value was selected
     */
    protected function addStatusHistoryComment()
    {
        $returnArray = $this->getReturnArrayWithObjects();
        if (empty($returnArray)) {
            return;
        }
        foreach ($returnArray as $object) {
            try {
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderFactory->create()->load($object['entity_id']);
                if ($order->getId()) {
                    $order->addStatusHistoryComment(
                        $this->getProfile()->getExportActionAddComment(),
                        false
                    )->setIsCustomerNotified(0);
                    $order->save();
                }
            } catch (\Exception $e) {
                $this->xtentoLogger->warning(
                    'Exception catched while adding order status history comment for order# ' . $object['increment_id'] . ': ' . $e->getMessage(
                    )
                );
                continue;
            }
        }
    }

    /**
     * Cancel exported orders, if enable in profile
     *
     * @return $this
     */
    protected function cancelOrder()
    {
        if (!$this->getProfile()->getExportActionCancelOrder()) {
            return $this;
        }
        $returnArray = $this->getReturnArrayWithObjects();
        if (empty($returnArray)) {
            return $this;
        }
        foreach ($returnArray as $object) {
            try {
                $order = $this->orderFactory->create()->load($object['entity_id']);
                if ($order->getId()) {
                    if (!$order->canCancel()) {
                        $this->getLogEntry()->addResultMessage(
                            __('Order #%1 could not be canceled because Magento does not allowing canceling it. Probably the order is holded, already invoiced or order status does not allowing canceling. If you dont see the Cancel button when viewing the order in Magento, you cannot cancel it using our extension either.', $object['increment_id'])
                        );
                    } else {
                        $order->cancel()->save();
                    }
                }
            } catch (\Exception $e) {
                $this->xtentoLogger->warning(
                    'Exception catched while cancelling order# ' . $object['increment_id'] . ': ' . $e->getMessage()
                );
                continue;
            }
        }
        return $this;
    }

    /**
     * Set order status/state for order
     *
     * @param $order \Magento\Sales\Model\Order
     * @param $newOrderStatus
     * @return bool
     */
    public function setOrderState($order, $newOrderStatus)
    {
        // Check current order state with priority, as a status can be assigned to multiple states
        foreach ($this->orderStatuses->getStates() as $state => $label) {
            if ($state == $order->getState()) {
                $stateStatuses = $this->orderStatuses->getStateStatuses($state, false);
                foreach ($stateStatuses as $status) {
                    if ($status == $newOrderStatus) {
                        // Get order status history commment to add
                        $orderStatusHistoryComment = '';
                        if ($this->getProfile()->getExportActionAddComment() != '') {
                            $orderStatusHistoryComment = $this->getProfile()->getExportActionAddComment();
                        }
                        // Change state/status
                        $order->setData('state', $state);
                        $order->setStatus($newOrderStatus);
                        $order->addStatusHistoryComment(
                            $orderStatusHistoryComment,
                            false
                        )->setIsCustomerNotified(0);
                        return true; // Correct state already set
                    }
                }
                break;
            }
        }
        // Status to set is from different state, find state and set
        foreach ($this->orderStatuses->getStates() as $state => $label) {
            $stateStatuses = $this->orderStatuses->getStateStatuses($state, false);
            foreach ($stateStatuses as $status) {
                if ($status == $newOrderStatus) {
                    // Get order status history commment to add
                    $orderStatusHistoryComment = '';
                    if ($this->getProfile()->getExportActionAddComment() != '') {
                        $orderStatusHistoryComment = $this->getProfile()->getExportActionAddComment();
                    }
                    // Change state/status
                    $order->setData('state', $state);
                    $order->setStatus($newOrderStatus);
                    $order->addStatusHistoryComment(
                        $orderStatusHistoryComment,
                        false
                    )->setIsCustomerNotified(0);
                    return true; // Status changed
                }
            }
        }
        // Order state not found - status maybe not assigned to a state
        $order->setStatus($newOrderStatus);
        return false;
    }

    /**
     * Save export log
     */
    protected function saveLog()
    {
        $this->getProfile()->saveLastExecutionNow();
        if (is_array($this->getFiles())) {
            $this->getLogEntry()->setFiles(implode("|", $this->getFiles()));
        }
        $this->getLogEntry()->setResult(
            $this->getLogEntry()->getResult() ? $this->getLogEntry()->getResult() : Log::RESULT_SUCCESSFUL
        );
        $this->getLogEntry()->setResultMessage(
            $this->getLogEntry()->getResultMessages() ? $this->getLogEntry()->getResultMessages() : __(
                'Export of %1 %2s finished in %3 seconds.',
                $this->getLogEntry()->getRecordsExported(),
                $this->getProfile()->getEntity(),
                (time() - $this->getBeginTime())
            )
        );
        if ($this->getLogEntry()->getResult() == Log::RESULT_SUCCESSFUL && $this->getLogEntry()->getRecordsExported() == 0) {
            $this->getLogEntry()->delete();
        } else {
            $this->getLogEntry()->save();
        }
        $this->errorEmailNotification();
        #$this->_registry->unregister('orderexport_log');
    }

    /**
     * On exception, send error email to debug email set in configuration
     *
     * @return $this
     */
    public function errorEmailNotification()
    {
        if (!$this->moduleHelper->isDebugEnabled() || $this->moduleHelper->getDebugEmail() == '') {
            return $this;
        }
        if ($this->getLogEntry()->getResult() >= Log::RESULT_WARNING) {
            try {
                /** @var \Magento\Framework\Mail\Message $message */
                $message = $this->objectManager->create('Magento\Framework\Mail\MessageInterface');
                $message->setFrom($this->scopeConfig->getValue('trans_email/ident_general/email'), $this->scopeConfig->getValue('trans_email/ident_general/name'));
                foreach (explode(",", $this->moduleHelper->getDebugEmail()) as $emailAddress) {
                    $emailAddress = trim($emailAddress);
                    $message->addTo($emailAddress, $emailAddress);
                }
                $message->setSubject('Magento Order Export Module @ ' . $this->request->getServer('SERVER_NAME'));
                $message->setBody('Warning/Error/Message(s): ' . $this->getLogEntry()->getResultMessages());
                $this->objectManager->create('\Magento\Framework\Mail\TransportInterfaceFactory')->create(['message' => clone $message])->sendMessage();
            } catch (\Exception $e) {
                $this->getLogEntry()->addResultMessage('Exception: ' . $e->getMessage());
                $this->getLogEntry()->setResult(Log::RESULT_WARNING);
                $this->getLogEntry()->setResultMessage($this->getLogEntry()->getResultMessages());
                $this->getLogEntry()->save();
            }
        }
        return $this;
    }

    /**
     * Check module status
     *
     * @throws LocalizedException
     */
    protected function checkStatus()
    {
        if (!$this->moduleHelper->confirmEnabled(true)) {
            throw new LocalizedException(__(str_rot13('Gur Beqre Rkcbeg Zbqhyr vf abg ranoyrq. Cyrnfr znxr fher lbh\'er hfvat n inyvq yvprafr xrl naq gung gur zbqhyr unf orra ranoyrq ng Flfgrz > KGRAGB Rkgrafvbaf > Fnyrf Rkcbeg pbasvthengvba.')));
        }
    }

    /**
     * Check if experimental features are enabled - not yet ported to M2
     *
     * @return bool
     */
    protected function getExperimentalFeatureSupport()
    {
        return false;
    }
}