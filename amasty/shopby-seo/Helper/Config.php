<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Helper;

use Amasty\ShopbySeo\Model\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * @deprecared usage of helpers is deprecated use \Amasty\ShopbySeo\Model\ConfigProvider instead
 * @see \Amasty\ShopbySeo\Model\ConfigProvider
 */
class Config
{
    public const MODULE_PATH = 'amasty_shopby_seo/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_PATH . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @deprecared use \Amasty\ShopbySeo\Model\ConfigProvider::isSeoUrlEnabled
     */
    public function isSeoUrlEnabled($storeId = null)
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->isSeoUrlEnabled($storeId);
    }

    /**
     * @deprecared use \Amasty\ShopbySeo\Model\ConfigProvider::isGenerateSeoByDefault
     */
    public function isGenerateSeoByDefault(?int $storeId = null): bool
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->isGenerateSeoByDefault($storeId);
    }

    /**
     * @return string
     * @deprecared use \Amasty\ShopbySeo\Model\ConfigProvider::getOptionSeparator
     */
    public function getOptionSeparator()
    {
        return ObjectManager::getInstance()->get(ConfigProvider::class)->getOptionSeparator();
    }
}
