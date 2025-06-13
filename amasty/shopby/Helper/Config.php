<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const MODULE_PATH = 'amshopby/';

    /**
     * @return bool
     */
    public function isCategoryFilterEnabled()
    {
        return (bool)$this->getModuleConfig('category_filter/enabled');
    }

    /**
     * @return bool
     */
    public function isEnabledShowOutOfStock()
    {
        return (bool)$this->getMagentoConfig('cataloginventory/options/show_out_of_stock');
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->getMagentoConfig(self::MODULE_PATH . $path, $storeId);
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getMagentoConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
