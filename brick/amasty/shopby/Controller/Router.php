<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Controller;

use Amasty\ShopbyBase\Model\ConfigProvider as BaseConfigProvider;
use Amasty\ShopbySeo\Model\ConfigProvider as SeoConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var SeoConfigProvider
     */
    private $seoConfigProvider;

    /**
     * @var BaseConfigProvider
     */
    private $baseConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        SeoConfigProvider $seoConfigProvider,
        BaseConfigProvider $baseConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->seoConfigProvider = $seoConfigProvider;
        $this->baseConfig = $baseConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        if (!$this->baseConfig->isAllProductsEnabled()) {
            return false;
        }

        $identifier = trim($request->getPathInfo(), '/');

        if ($this->seoConfigProvider->isAddSuffix()
            && ($seoSuffix = $this->getCatalogSeoSuffix())
        ) {
            $suffixPosition = strpos($identifier, $seoSuffix);
            if ($suffixPosition !== false) {
                $identifier = substr($identifier, 0, $suffixPosition);
            }
        }

        if ($this->checkMatchExpressions($request, $identifier)) {
            $request->setModuleName('amshopby')
                ->setControllerModule('Amasty_Shopby')
                ->setRouteName('amshopby')
                ->setControllerName('index')
                ->setActionName('index')
                ->setAlias(
                    \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                    $identifier
                );

            return $this->actionFactory->create(\Amasty\Shopby\Controller\Index\Index::class);
        }

        return false;
    }

    private function getCatalogSeoSuffix()
    {
        return (string)$this->scopeConfig->getValue(
            \Amasty\Shopby\Helper\Data::CATALOG_SEO_SUFFIX_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param RequestInterface $request
     * @param string $identifier
     * @return bool
     */
    public function checkMatchExpressions(RequestInterface $request, $identifier)
    {
        return $identifier == $this->baseConfig->getAllProductsUrlKey();
    }
}
