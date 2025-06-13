<?php

/**
 * Product:       Xtento_XtCore
 * ID:            %!uniqueid!%
 * Last Modified: 2022-08-15T19:11:41+00:00
 * File:          Setup/Patch/Data/SaveInstallDate.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

declare(strict_types=1);

namespace Xtento\XtCore\Setup\Patch\Data;

use Magento\Framework\Exception\SessionException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SaveInstallDate implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * Config Value Factory
     *
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $configValueFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configValueFactory = $configValueFactory;
        $this->appState = $appState;
    }

    /**
     * @return void|SaveInstallDate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'saveInstallDate'], []);
    }

    public function saveInstallDate()
    {
        /** @var $configValue \Magento\Framework\App\Config\ValueInterface */
        $configValue = $this->configValueFactory->create();
        $configValue->load('xtcore/adminnotification/installation_date', 'path');
        $configValue->setValue((string)time())->setPath('xtcore/adminnotification/installation_date')->save();
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
