<?php

/**
 * Product:       Xtento_XtCore
 * ID:            %!uniqueid!%
 * Last Modified: 2019-10-04T12:21:08+00:00
 * File:          Helper/Cron.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Helper;

class Cron extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CRON_PATH_PREFIX = 'crontab/%s/jobs/xtento_';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Xtento\XtCore\Model\ResourceModel\Config
     */
    protected $xtCoreConfig;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Cron constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Xtento\XtCore\Model\ResourceModel\Config $xtCoreConfig
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Xtento\XtCore\Model\ResourceModel\Config $xtCoreConfig,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
        $this->xtCoreConfig = $xtCoreConfig;
        $this->configValueFactory = $configValueFactory;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get timestapm when the XtCore module was installed for the first time
     *
     * @return mixed
     */
    public function getInstallationDate()
    {
        return $this->scopeConfig->getValue('xtcore/adminnotification/installation_date');
    }

    public function isCronRunning()
    {
        $lastExecution = $this->getLastCronExecution();
        if (empty($lastExecution)) {
            return false;
        }
        $differenceInSeconds = $this->getTimestamp() - $lastExecution;
        // If the cronjob has been executed within the last 15 minutes, return true
        return $differenceInSeconds < (60 * 15);
    }

    public function getLastCronExecution()
    {
        return $this->xtCoreConfig->getConfigValue('xtcore/crontest/last_execution');
    }

    public function getTimestamp()
    {
        return (string)time();
    }

    /**
     * Add cronjob to database
     *
     * @param $cronIdentifier
     * @param $cronExpression
     * @param $cronRunModel
     * @param $cronGroup
     * @return $this
     */
    public function addCronjob($cronIdentifier, $cronExpression, $cronRunModel, $cronGroup = 'default')
    {
        $this->configValueFactory->create()->load(
            $this->getCronExpressionConfigPath($cronIdentifier, $cronGroup),
            'path'
        )->setValue(
            $cronExpression
        )->setPath(
            $this->getCronExpressionConfigPath($cronIdentifier, $cronGroup)
        )->save();

        $this->configValueFactory->create()->load(
            $this->getCronRunModelConfigPath($cronIdentifier, $cronGroup),
            'path'
        )->setValue(
            $cronRunModel
        )->setPath(
            $this->getCronRunModelConfigPath($cronIdentifier, $cronGroup)
        )->save();

        return $this;
    }

    /**
     * Remove cronjob from database
     *
     * @param $cronIdentifier
     * @param $cronGroup
     * @return $this
     */
    public function removeCronjob($cronIdentifier, $cronGroup = 'default')
    {
        $this->configValueFactory->create()
            ->load($this->getCronExpressionConfigPath($cronIdentifier, $cronGroup), 'path')->delete();
        $this->configValueFactory->create()
            ->load($this->getCronRunModelConfigPath($cronIdentifier, $cronGroup), 'path')->delete();

        if ($cronGroup != 'default') {
            // Remove legacy cronjobs
            $this->configValueFactory->create()
                ->load($this->getCronExpressionConfigPath($cronIdentifier, 'default'), 'path')->delete();
            $this->configValueFactory->create()
                ->load($this->getCronRunModelConfigPath($cronIdentifier, 'default'), 'path')->delete();
        }

        return $this;
    }

    /**
     *
     * Remove cronjobs "like" from database,
     * $cronIdentifier should contain %
     *
     * @param $cronIdentifier
     * @param $cronGroup
     *
     * @return $this
     */
    public function removeCronjobsLike($cronIdentifier, $cronGroup = 'default')
    {
        if (empty($cronIdentifier)) {
            return $this;
        }

        $configTable = $this->resourceConnection->getTableName('core_config_data');
        $connection = $this->resourceConnection->getConnection();
        $connection->delete($configTable, ['path LIKE ?' => sprintf(self::CRON_PATH_PREFIX, $cronGroup) . $cronIdentifier]);

        if ($cronGroup != 'default') {
            // Remove legacy cronjobs
            $connection->delete($configTable, ['path LIKE ?' => sprintf(self::CRON_PATH_PREFIX, 'default') . $cronIdentifier]);
        }

        return $this;
    }

    /**
     * Get config path to save cron expression in
     *
     * @param $cronIdentifier
     * @param $cronGroup
     * @return string
     */
    protected function getCronExpressionConfigPath($cronIdentifier, $cronGroup)
    {
        return sprintf(self::CRON_PATH_PREFIX, $cronGroup) . $cronIdentifier . '/schedule/cron_expr';
    }

    /**
     * Get config path to save cron run model in
     *
     * @param $cronIdentifier
     * @param $cronGroup
     *
     * @return string
     */
    protected function getCronRunModelConfigPath($cronIdentifier, $cronGroup)
    {
        return sprintf(self::CRON_PATH_PREFIX, $cronGroup) . $cronIdentifier . '/run/model';
    }
}
