<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface;
use Amasty\ShopbyBase\Model\UrlBuilder\UrlModifier;

class UrlBuilder implements UrlBuilderInterface
{
    public const DEFAULT_ORDER = 100;

    /**
     * @var array
     */
    private $storage = [];

    /**
     * @var AdapterInterface[]
     */
    private $urlAdapters = [];

    /**
     * @var UrlModifier
     */
    private $urlModifier;

    public function __construct(
        UrlModifier $urlModifier,
        $urlAdapters = []
    ) {
        $this->urlModifier = $urlModifier;
        $this->initAdapters($urlAdapters);
    }

    public function getUrl(
        ?string $routePath = null,
        ?array $routeParams = null,
        bool $skipUrlModify = false,
        ?int $categoryId = null
    ): ?string {
        $key = $this->getKey($routePath, $routeParams);
        if (!isset($this->storage[$key])) {
            $url = null;
            foreach ($this->urlAdapters as $adapter) {
                if ($adapter->isApplicable($routePath, $routeParams)
                    && ($url = $adapter->getUrl($routePath, $routeParams))
                ) {
                    break;
                }
            }

            if (!$skipUrlModify && $url) {
                $url = $this->urlModifier->execute($url, $categoryId);
            }

            $this->storage[$key] = $url;
        }

        return $this->storage[$key];
    }

    private function getKey(?string $routePath = null, ?array $routeParams = null): string
    {
        $key = '' . $routePath;
        if ($routeParams !== null) {
            $key .= json_encode($this->sortRouteParamsForKey($routeParams));
        }

        return $key;
    }

    private function sortRouteParamsForKey(array $routeParams): array
    {
        foreach ($routeParams as $key => $routeParam) {
            if (is_array($routeParam)) {
                $routeParams[$key] = $this->sortRouteParamsForKey($routeParam);
            }
        }
        ksort($routeParams);

        return $routeParams;
    }

    /**
     * @param bool $modified = true
     * @return string|null
     */
    public function getCurrentUrl($modified = true)
    {
        $url = null;
        foreach ($this->urlAdapters as $adapter) {
            if (method_exists($adapter, 'getCurrentUrl')) {
                $url = $adapter->getCurrentUrl();
                break;
            }
        }

        if ($modified && $url) {
            $url = $this->urlModifier->execute($url);
        }

        return $url;
    }

    /**
     * @param array $params
     * @return string|null
     */
    public function getBaseUrl($params = [])
    {
        $url = null;
        foreach ($this->urlAdapters as $adapter) {
            if (method_exists($adapter, 'getBaseUrl')) {
                $url = $adapter->getBaseUrl($params);
                break;
            }
        }

        return $url;
    }

    /**
     * @param array $urlAdapters
     * @return $this
     */
    private function initAdapters(array $urlAdapters = [])
    {
        foreach ($urlAdapters as $urlAdapter) {
            if (isset($urlAdapter['adapter'])
                && ($urlAdapter['adapter'] instanceof AdapterInterface)
            ) {
                $order = isset($urlAdapter['sort_order']) ? $urlAdapter['sort_order'] : self::DEFAULT_ORDER;
                $this->urlAdapters[$order] = $urlAdapter['adapter'];
            }
        }
        ksort($this->urlAdapters, SORT_NUMERIC);
        return $this;
    }
}
