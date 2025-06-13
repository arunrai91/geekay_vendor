<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\SysInfo\Provider\Collector\AdditionalInfoRequest;

use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Model\SysInfo\Provider\Collector\CollectorInterface;

class BaseVersion implements CollectorInterface
{
    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    public function get()
    {
        $moduleInfo = $this->moduleInfoProvider->getModuleInfo('Amasty_Base');

        return $moduleInfo[ModuleInfoProvider::MODULE_VERSION_KEY] ?? '';
    }
}
