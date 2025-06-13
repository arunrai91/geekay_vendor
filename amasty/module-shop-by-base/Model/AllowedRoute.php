<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\ShopbyBase\Helper\Data;

class AllowedRoute
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var  \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Amasty\ShopbyBase\Model\ConfigProvider $configProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->configProvider = $configProvider;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function isRouteAllowed(RequestInterface $request)
    {
        if ($this->isEnabled()) {
            return true;
        }

        $brandCode = $this->getBrandCode();
        if ($brandCode) {
            $seoParams = $this->registry->registry(Data::SHOPBY_SEO_PARSED_PARAMS);
            $seoBrandPresent = isset($seoParams) && array_key_exists($brandCode, $seoParams);
            if ($request->getParam($brandCode) || $seoBrandPresent) {
                return true;
            }
        }

        $this->registry->unregister(Data::SHOPBY_SEO_PARSED_PARAMS);

        return false;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->configProvider->isAllProductsEnabled();
    }

    /**
     * @return mixed
     */
    public function getBrandCode()
    {
        return $this->scopeConfig->getValue('amshopby_brand/general/attribute_code', ScopeInterface::SCOPE_STORE);
    }
}
