<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-06-02T19:39:39+00:00
 * File:          Controller/Adminhtml/Manual/ManualPost.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Controller\Adminhtml\Manual;

use Magento\Framework\Exception\LocalizedException;

class ManualPost extends \Xtento\OrderExport\Controller\Adminhtml\Manual
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Xtento\OrderExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Xtento\XtCore\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * ManualPost constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Helper\Entity $entityHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Xtento\OrderExport\Model\ExportFactory $exportFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Helper\Entity $entityHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory
    ) {
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $scopeConfig, $profileFactory);
        $this->storeManager = $storeManager;
        $this->entityHelper = $entityHelper;
        $this->dateHelper = $dateHelper;
        $this->localeDate = $localeDate;
        $this->registry = $registry;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
        $this->utilsHelper = $utilsHelper;
        $this->exportFactory = $exportFactory;
    }

    /**
     * Export action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     * @throws \Exception
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getPost('profile_id');
        $profile = $this->profileFactory->create()->load($profileId);
        if (!$profile->getId()) {
            $this->messageManager->addErrorMessage(__('No profile selected or this profile does not exist anymore.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        // Table prefix
        $tablePrefix = 'main_table.';
        // Prepare filters
        $filters = [];
        if ($this->getRequest()->getPost('store_id') !== null) {
            $storeIds = [];
            $postStoreIds = $this->getRequest()->getPost('store_id');
            if (isset($postStoreIds[0]) && strstr($postStoreIds[0], ',')) {
                $postStoreIds = explode(",", $postStoreIds[0]); // Comma multi select fix for file download JS
            }
            foreach ($postStoreIds as $storeId) {
                if ($storeId != '0' && $storeId != '') {
                    array_push($storeIds, $storeId);
                }
            }
            if (!empty($storeIds)) {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $websiteIds = [];
                    foreach ($storeIds as $storeId) {
                        array_push($websiteIds, $this->storeManager->getStore($storeId)->getWebsiteId());
                    }
                    $filters[] = ['website_id' => ['in' => $websiteIds]];
                } else {
                    $filters[] = [$tablePrefix . 'store_id' => ['in' => $storeIds]];
                }
            }
        }
        if ($this->getRequest()->getPost('status') !== null) {
            $statuses = [];
            $postStatuses = $this->getRequest()->getPost('status');
            if (isset($postStatuses[0]) && strstr($postStatuses[0], ',')) {
                $postStatuses = explode(",", $postStatuses[0]); // Comma multi select fix for file download JS
            }
            foreach ($postStatuses as $status) {
                if ($status !== '') {
                    array_push($statuses, $status);
                }
            }
            if (!empty($statuses)) {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
                    $filters[] = [$tablePrefix . 'status' => ['in' => $statuses]];
                } else {
                    if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA) {
                        $filters[] = [$tablePrefix . 'rma_status' => ['in' => $statuses]];
                    } else {
                        $filters[] = [$tablePrefix . 'state' => ['in' => $statuses]];
                    }
                }
            }
        }
        if ($this->getRequest()->getPost('increment_from') !== null) {
            $collection = $this->_objectManager->create(
                $this->entityHelper->getExportEntity($profile->getEntity())
            )->getCollection();
            if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
                $collection->addFieldToSelect('entity_id')
                    ->addFieldToFilter('entity_id', $this->getRequest()->getPost('increment_from'));
            } else {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $collection->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('entity_id', $this->getRequest()->getPost('increment_from'));
                } else {
                    if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_AWRMA) {
                        $collection->addFieldToSelect('id')
                            ->addFieldToFilter('id', $this->getRequest()->getPost('increment_from'));
                    } else {
                        if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA) {
                            $collection->addFieldToSelect('rma_id')
                                ->addFieldToFilter('rma_id', $this->getRequest()->getPost('increment_from'));
                        } else {
                            $collection->addAttributeToSelect('entity_id')
                                ->addAttributeToFilter('increment_id', $this->getRequest()->getPost('increment_from'));
                        }
                    }
                }
            }
            $object = $collection->getFirstItem();
            if ($object && $object->getId()) {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $filters[] = ['entity_id' => ['from' => $object->getId()]];
                } else {
                    if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_AWRMA) {
                        $filters[] = ['id' => ['from' => $object->getId()]];
                    } else {
                        if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA) {
                            $filters[] = ['rma_id' => ['from' => $object->getId()]];
                        } else {
                            $filters[] = [$tablePrefix . 'entity_id' => ['from' => $object->getId()]];
                        }
                    }
                }
            } else {
                if ($this->getRequest()->getPost('increment_from') != 1) {
                    $this->messageManager->addErrorMessage(
                        __('The supplied starting increment_id does not exist. Use 1 to export from the beginning.')
                    );
                    /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultFactory->create(
                        \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                    );
                    $resultRedirect->setPath('xtento_orderexport/manual/index', ['profile_id' => $profile->getId()]);
                    return $resultRedirect;
                }
            }
        }
        if ($this->getRequest()->getPost('increment_to') !== null) {
            $collection = $this->_objectManager->create(
                $this->entityHelper->getExportEntity($profile->getEntity())
            )->getCollection();
            if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
                $collection->addFieldToSelect('entity_id')
                    ->addFieldToFilter('entity_id', $this->getRequest()->getPost('increment_to'));
            } else {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $collection->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('entity_id', $this->getRequest()->getPost('increment_to'));
                } else {
                    if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_AWRMA) {
                        $collection->addFieldToSelect('id')
                            ->addFieldToSelect('id', $this->getRequest()->getPost('increment_to'));
                    } else {
                        if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA) {
                            $collection->addFieldToSelect('rma_id')
                                ->addFieldToSelect('rma_id', $this->getRequest()->getPost('increment_to'));
                        } else {
                            $collection->addAttributeToSelect('entity_id')
                                ->addAttributeToFilter('increment_id', $this->getRequest()->getPost('increment_to'));
                        }
                    }
                }
            }
            $object = $collection->getFirstItem();
            if ($object && $object->getId()) {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $filters[] = ['entity_id' => ['to' => $object->getId()]];
                } else {
                    if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_AWRMA) {
                        $filters[] = ['id' => ['to' => $object->getId()]];
                    } else {
                        if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA) {
                            $filters[] = ['rma_id' => ['to' => $object->getId()]];
                        } else {
                            $filters[] = [$tablePrefix . 'entity_id' => ['to' => $object->getId()]];
                        }
                    }
                }
            } else {
                if ($this->getRequest()->getPost('increment_to') != 0) {
                    $this->messageManager->addErrorMessage(
                        __('The supplied ending increment_id does not exist. Use 0 to export until the end.')
                    );
                    /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultFactory->create(
                        \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                    );
                    $resultRedirect->setPath('xtento_orderexport/manual/index', ['profile_id' => $profile->getId()]);
                    return $resultRedirect;
                }
            }
        }
        $dateNormalizer = new \Magento\Framework\Data\Form\Filter\Date(
            $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT), $this->_localeResolver
        );
        $dateRangeFilter = [];
        if ($this->getRequest()->getPost('daterange_from') != '') {
            $dateRangeFilter['datetime'] = true;
            $fromDate = $dateNormalizer->inputFilter($this->getRequest()->getPost('daterange_from'));
            $fromDate = $this->localeDate->scopeDate(null, $fromDate, true);
            $fromDate->setTimezone(new \DateTimeZone('UTC'));
            $dateRangeFilter['from'] = $fromDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        if ($this->getRequest()->getPost('daterange_to') != '') {
            $dateRangeFilter['datetime'] = true;
            $toDate = $dateNormalizer->inputFilter($this->getRequest()->getPost('daterange_to'));
            $toDate = $this->localeDate->scopeDate(null, $toDate, true);
            $toDate->add(new \DateInterval('P1D'));
            $toDate->sub(new \DateInterval('PT1S')); // So the "next day, 12:00:00am" is not included
            $toDate->setTimezone(new \DateTimeZone('UTC'));
            $dateRangeFilter['to'] = $toDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        if (empty($dateRangeFilter) && $this->getRequest()->getPost('last_x_days') != '') {
            $profileFilterCreatedLastXDays = $this->getRequest()->getPost('last_x_days');
            if (!empty($profileFilterCreatedLastXDays) || $profileFilterCreatedLastXDays == '0') {
                $profileFilterCreatedLastXDays = preg_replace('/[^0-9]/', '', $profileFilterCreatedLastXDays);
                if ($profileFilterCreatedLastXDays >= 0) {
                    $dateToday = $this->localeDate->date();
                    $dateToday->sub(new \DateInterval('P' . $profileFilterCreatedLastXDays . 'D'));
                    $dateToday->setTime(0, 0, 0);
                    $dateToday->setTimezone(new \DateTimeZone('UTC'));
                    $dateRangeFilter['datetime'] = true;
                    $dateRangeFilter['from'] = $dateToday->format(
                        \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT
                    );
                }
            }
        }
        $profileFilterOlderThanXMinutes = $profile->getData('export_filter_older_x_minutes');
        if (!empty($profileFilterOlderThanXMinutes)) {
            $profileFilterOlderThanXMinutes = intval(preg_replace('/[^0-9]/', '', $profileFilterOlderThanXMinutes));
            if ($profileFilterOlderThanXMinutes > 0) {
                $dateToday = $this->localeDate->date();
                $dateToday->sub(new \DateInterval('PT' . $profileFilterOlderThanXMinutes . 'M'));
                $dateToday->setTimezone(new \DateTimeZone('UTC'));
                $dateRangeFilter['datetime'] = true;
                $dateRangeFilter['to'] = $dateToday->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }
        }
        if (!empty($dateRangeFilter)) {
            if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                $filters[] = ['created_at' => $dateRangeFilter];
            } else {
                $filters[] = [$tablePrefix . 'created_at' => $dateRangeFilter];
            }
        }
        #var_dump($filters); die();
        // Export
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setPath($this->_getSession()->getCookiePath())
            ->setDomain($this->_getSession()->getCookieDomain());
        try {
            $beginTime = time();
            $exportModel = $this->exportFactory->create()->setProfile($profile);
            if ($this->getRequest()->getPost('force_status') != '') {
                $exportModel->setForceChangeStatus($this->getRequest()->getPost('force_status'));
            }
            if ($this->getRequest()->getPost('filter_new_only') == 'on') {
                $exportModel->setExportFilterNewOnly(true);
            }
            $exportedFiles = $exportModel->manualExport($filters);
            $endTime = time();
            $successMessage = __(
                'Export of %1 %2s completed successfully in %3 seconds. ' .
                'Click <a href="%4">here</a> to download exported files.',
                $this->registry->registry('orderexport_log')->getRecordsExported(),
                $profile->getEntity(),
                ($endTime - $beginTime),
                $this->getUrl(
                    'xtento_orderexport/log/download',
                    ['id' => $this->registry->registry('orderexport_log')->getId()]
                )
            );
            if ($this->getRequest()->getPost('start_download', false)) {
                $this->cookieManager->setPublicCookie('fileDownload', 'true', $cookieMetadata);
                $this->cookieManager->setPublicCookie('lastMessage', $successMessage, $cookieMetadata);
                if ($this->registry->registry('orderexport_log')->getResult(
                    ) !== \Xtento\OrderExport\Model\Log::RESULT_SUCCESSFUL
                ) {
                    $this->cookieManager->setPublicCookie(
                        'lastErrorMessage',
                        __(nl2br($this->registry->registry('orderexport_log')->getResultMessage())),
                        $cookieMetadata
                    );
                } else {
                    $this->cookieManager->setPublicCookie('lastErrorMessage', '', $cookieMetadata);
                }
                /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $file = $this->utilsHelper->prepareFilesForDownload($exportedFiles);
                if (empty($file)) {
                    throw new LocalizedException(__('No files have been exported. Please check your XSL Template and/or profile filters.'));
                }
                $resultPage->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Content-type', 'application/octet-stream', true)
                    ->setHeader('Content-Length', strlen($file['data']))
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $file['filename'] . '"')
                    ->setHeader('Last-Modified', date('r'));
                $resultPage->setContents($file['data']);
                return $resultPage;
            } else {
                $this->messageManager->addComplexSuccessMessage('backendHtmlMessage',
                    [
                        'html' => (string)$successMessage
                    ]
                );
                if ($this->registry->registry('orderexport_log')->getResult(
                    ) !== \Xtento\OrderExport\Model\Log::RESULT_SUCCESSFUL
                ) {
                    $this->messageManager->addErrorMessage(
                        __(nl2br($this->registry->registry('orderexport_log')->getResultMessage()))
                    );
                }
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                $resultRedirect->setPath('xtento_orderexport/manual/index', ['profile_id' => $profile->getId()]);
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            if ($this->getRequest()->getPost('start_download', false)) {
                $this->cookieManager->setPublicCookie('lastErrorMessage', __(nl2br($e->getMessage())), $cookieMetadata);
                $this->cookieManager->setPublicCookie('lastMessage', false, $cookieMetadata);
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $resultPage->setContents('failed');
                return $resultPage;
            } else {
                $this->messageManager->addWarningMessage(__('%1', nl2br($e->getMessage())));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('xtento_orderexport/manual/index', ['profile_id' => $profile->getId()]);
                return $resultRedirect;
            }
        }
    }
}