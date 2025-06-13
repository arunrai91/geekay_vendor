<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Model\Indexer\Design;

use Amasty\Rolepermissions\Plugin\Store\Model\WebsiteRepository;
use Amasty\Rolepermissions\Plugin\StoreManager;
use Magento\Framework\Registry;
use Magento\Theme\Model\Indexer\Design\Config as DesignConfig;

class Config
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    public function beforeExecuteFull(DesignConfig $subject)
    {
        $this->coreRegistry->register(WebsiteRepository::AM_USE_ALL_WEBSITES, true, true);
        $this->coreRegistry->register(StoreManager::AM_SKIP_STORES_PLUGIN, true, true);
    }

    public function afterExecuteFull(DesignConfig $subject)
    {
        $this->coreRegistry->unregister(WebsiteRepository::AM_USE_ALL_WEBSITES);
        $this->coreRegistry->unregister(StoreManager::AM_SKIP_STORES_PLUGIN);
    }
}
