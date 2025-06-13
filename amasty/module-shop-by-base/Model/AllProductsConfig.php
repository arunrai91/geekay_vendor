<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Helper\PermissionHelper;

class AllProductsConfig
{
    /**
     * Local cache.
     *
     * @var bool
     */
    private $storage = null;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var PermissionHelper
     */
    private $permissionHelper;

    public function __construct(ConfigProvider $configProvider, PermissionHelper $permissionHelper)
    {
        $this->configProvider = $configProvider;
        $this->permissionHelper = $permissionHelper;
    }

    /**
     * Is "all products" functionality allowed to use.
     *
     * @return bool
     */
    public function isAllProductsAvailable(): bool
    {
        if ($this->storage === null) {
            $this->storage = $this->configProvider->isAllProductsEnabled()
                && $this->permissionHelper->checkPermissions();
        }

        return $this->storage;
    }
}
