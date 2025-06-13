<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2020-04-07T18:36:27+00:00
 * File:          Model/AbstractAutomaticExport.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model;

abstract class AbstractAutomaticExport extends \Magento\Framework\Model\AbstractModel
{
    /*
     * Add store, date, status, ... filters based on profile settings
     */

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $xtCoreUtilsHelper;

    /**
     * @var \Xtento\OrderExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Xtento\OrderExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * @var \Xtento\OrderExport\Logger\Logger
     */
    protected $xtentoLogger;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory
     */
    protected $localeDateFactory;

    /**
     * @var \Xtento\XtCore\Helper\Cron
     */
    protected $cronHelper;

    /**
     * AbstractAutomaticExport constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Utils $xtCoreUtilsHelper
     * @param \Xtento\OrderExport\Helper\Module $moduleHelper
     * @param ProfileFactory $profileFactory
     * @param ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param ExportFactory $exportFactory
     * @param \Xtento\OrderExport\Logger\Logger $xtentoLogger
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory $localeDateFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Utils $xtCoreUtilsHelper,
        \Xtento\OrderExport\Helper\Module $moduleHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Xtento\OrderExport\Model\ExportFactory $exportFactory,
        \Xtento\OrderExport\Logger\Logger $xtentoLogger,
        \Magento\Store\Model\StoreFactory $storeFactory,    
        \Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory $localeDateFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->xtCoreUtilsHelper = $xtCoreUtilsHelper;
        $this->storeFactory = $storeFactory;
        $this->moduleHelper = $moduleHelper;
        $this->exportFactory = $exportFactory;
        $this->xtentoLogger = $xtentoLogger;
        $this->profileFactory = $profileFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->localeDateFactory = $localeDateFactory;
        $this->cronHelper = $cronHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function addProfileFilters($profile)
    {
        /** @var \Magento\Framework\Stdlib\DateTime\Timezone $localeDate */
        $localeDate = $this->localeDateFactory->create();
        // Filters
        $filters = [];
        // Table prefix
        $tablePrefix = 'main_table.';
        // Filters
        $profileFilterStoreIds = explode(",", $profile->getStoreIds());
        if (!empty($profileFilterStoreIds)) {
            $storeIds = [];
            foreach ($profileFilterStoreIds as $storeId) {
                if ($storeId != '0' && $storeId != '') {
                    array_push($storeIds, $storeId);
                }
            }
            if (!empty($storeIds)) {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                    $websiteIds = [];
                    foreach ($storeIds as $storeId) {
                        array_push($websiteIds, $this->storeFactory->create()->load($storeId)->getWebsiteId());
                    }
                    $filters[] = ['website_id' => ['in' => $websiteIds]];
                } else {
                    $filters[] = [$tablePrefix . 'store_id' => ['in' => $storeIds]];
                }
            }
        }
        $profileFilterStatus = explode(",", $profile->getExportFilterStatus());
        if (!empty($profileFilterStatus)) {
            $statuses = [];
            foreach ($profileFilterStatus as $status) {
                if ($status !== '') {
                    array_push($statuses, $status);
                }
            }
            if (!empty($statuses)) {
                if ($profile->getEntity() == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
                    $filters[] = [$tablePrefix . 'status' => ['in' => $statuses]];
                } else {
                    $filters[] = [$tablePrefix . 'state' => ['in' => $statuses]];
                }
            }
        }
        $dateRangeFilter = [];
        $profileFilterDatefrom = $profile->getExportFilterDatefrom();
        if (!empty($profileFilterDatefrom)) {
            $dateRangeFilter['datetime'] = true;
            $fromDate = $localeDate->scopeDate(null, $profileFilterDatefrom, true);
            $fromDate->setTimezone(new \DateTimeZone('UTC'));
            $dateRangeFilter['from'] = $fromDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        $profileFilterDateto = $profile->getExportFilterDateto();
        if (!empty($profileFilterDateto)) {
            $dateRangeFilter['datetime'] = true;
            $toDate = $localeDate->scopeDate(null, $profileFilterDateto, true);
            $toDate->add(new \DateInterval('P1D'));
            $toDate->sub(new \DateInterval('PT1S')); // So the "next day, 12:00:00am" is not included
            $toDate->setTimezone(new \DateTimeZone('UTC'));
            $dateRangeFilter['to'] = $toDate->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        $profileFilterCreatedLastXDays = $profile->getData('export_filter_last_x_days');
        if (!empty($profileFilterCreatedLastXDays) || $profileFilterCreatedLastXDays == '0') {
            $profileFilterCreatedLastXDays = intval(preg_replace('/[^0-9]/', '', $profileFilterCreatedLastXDays));
            if ($profileFilterCreatedLastXDays >= 0) {
                $dateToday = $localeDate->date();
                $dateToday->sub(new \DateInterval('P' . $profileFilterCreatedLastXDays . 'D'));
                $dateToday->setTime(0, 0, 0);
                $dateToday->setTimezone(new \DateTimeZone('UTC'));
                $dateRangeFilter['datetime'] = true;
                $dateRangeFilter['from'] = $dateToday->format(
                    \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT
                );
            }
        }
        $profileFilterOlderThanXMinutes = $profile->getData('export_filter_older_x_minutes');
        if (!empty($profileFilterOlderThanXMinutes)) {
            $profileFilterOlderThanXMinutes = intval(preg_replace('/[^0-9]/', '', $profileFilterOlderThanXMinutes));
            if ($profileFilterOlderThanXMinutes > 0) {
                $dateToday = $localeDate->date();
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
        return $filters;
    }
}