<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\UrlBuilder;

use Amasty\ShopbyBase\Model\ConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class Adapter implements \Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface
{
    public const SELF_ROUTE_PATH = 'amshopby/index/index';
    public const SELF_MODULE_NAME = 'amshopby';
    public const SAME_PAGE_ROUTE = '*/*/*';

    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\UrlFactory $urlBuilderFactory,
        RequestInterface $request,
        ConfigProvider $configProvider
    ) {
        $this->urlBuilder = $urlBuilderFactory->create();
        $this->request = $request;
        $this->configProvider = $configProvider;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     * @return string|null
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $routePath = trim($routePath, '/');
        if ($routePath == self::SELF_ROUTE_PATH
            || ($this->request->getModuleName() == self::SELF_MODULE_NAME && $routePath == self::SAME_PAGE_ROUTE)
        ) {
            $urlKey = $this->configProvider->getAllProductsUrlKey();
            if ($urlKey) {
                if (isset($routeParams['_scope'])) {
                    $this->urlBuilder->setScope($routeParams['_scope']);
                } else {
                    $this->urlBuilder->setScope(null);
                }
                $routeParams['_direct'] = $urlKey . $this->getSuffix();
                $routePath = '';
            }
            return $this->urlBuilder->getUrl($routePath, $routeParams);
        }
        return null;
    }

    public function isApplicable(string $routePath = null, array $routeParams = null): bool
    {
        $routePath = trim($routePath, '/');
        return $routePath == self::SELF_ROUTE_PATH
            || ($this->request->getModuleName() == self::SELF_MODULE_NAME && $routePath == self::SAME_PAGE_ROUTE);
    }

    /**
     * @return null
     */
    public function getSuffix()
    {
        return null;
    }
}
