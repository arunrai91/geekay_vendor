<?php

/**
 * Product:       Xtento_XtCore
 * ID:            %!uniqueid!%
 * Last Modified: 2025-04-11T10:20:59+00:00
 * File:          Model/System/Config/Backend/Configuration.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Configuration
 * @package Xtento\XtCore\Model\System\Config\Backend
 */
class Configuration extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Xtento\XtCore\Helper\Server
     */
    protected $serverHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Configuration constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Xtento\XtCore\Helper\Server $serverHelper,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->serverHelper = $serverHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $updatedConfiguration
     * @return bool
     * @throws \Exception
     */
    public function afterUpdate($updatedConfiguration)
    {
        $sName = $this->serverHelper->getFirstName();
        $sName2 = $this->serverHelper->getSecondName();
        $s = trim((string)$this->registry->registry('xtento_configuration_license_key'));
        if (empty($s)) {
            try {
                // Try to get license key from database
                $s = trim((string)$this->scopeConfig->getValue($updatedConfiguration['config_path'] . 'serial'));
            } catch (\Exception $e) {}
        }
        if ($s !== sha1(sha1($updatedConfiguration['ext_id'] . '_' . $sName)) &&
            $s !== sha1(sha1($updatedConfiguration['ext_id'] . '_' . $sName2))
        ) {
            try {
                $configValue = $this->configValueFactory->create();
                /** @var $configValue \Magento\Framework\App\Config\Value */
                $configValue->load($updatedConfiguration['config_path'] . 'enabled', 'path');
                $configValue->setValue(0)->setPath($updatedConfiguration['config_path'] . 'enabled')->save();
            } catch (\Exception $e) {
                throw new LocalizedException(__('We can\'t save the module configuration: %1', $e->getMessage()));
            }
            $this->messageManager->addErrorMessage(
                __('The extension couldn\'t be enabled. Please make sure you are using a valid license key.')
            );
            return false;
        } else {
            return true;
        }
    }
}
