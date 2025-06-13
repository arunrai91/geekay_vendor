<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2019-08-27T13:36:53+00:00
 * File:          Helper/Module.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Helper;

class Module extends \Xtento\XtCore\Helper\AbstractModule
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Module constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context, $registry, $serverHelper, $utilsHelper);
        $this->resource = $resource;
    }

    protected $edition = '%!version!%';
    protected $module = 'Xtento_OrderExport';
    protected $extId = 'MTWOXtento_OrderExport917370';
    protected $configPath = 'orderexport/general/';

    // Module specific functionality below
    public function getDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag($this->configPath . 'debug');
    }

    public function isDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            $this->configPath . 'debug'
        ) && ($debug_email = $this->scopeConfig->getValue($this->configPath . 'debug_email')) && !empty($debug_email);
    }

    public function getDebugEmail()
    {
        return $this->scopeConfig->getValue($this->configPath . 'debug_email');
    }

    public function isModuleProperlyInstalled()
    {
        return true; // Not required, Magento 2 does the job of handling upgrades better than Magento 1
        // Check if DB table(s) have been created.
        return ($this->resource->getConnection('core_read')->showTableStatus(
                $this->resource->getTableName('xtento_orderexport_profile')
            ) !== false);
    }

    public function getExportBkpDir()
    {
        return rtrim($this->serverHelper->getBaseDir()->getAbsolutePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "export_bkp" . DIRECTORY_SEPARATOR;
    }
}
