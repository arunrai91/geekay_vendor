<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\UrlBuilder;

use Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface;

/**
 * Wrapper for url adapters with local storage by route.
 */
class CacheableAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $originalAdapter;

    /**
     * @var array ['route' => 'clearBuildedUrl', ...]
     */
    private $cache = [];

    public function __construct(AdapterInterface $originalAdapter)
    {
        $this->originalAdapter = $originalAdapter;
    }

    public function getUrl($routePath = null, $routeParams = null)
    {
        if (!is_array($routeParams)) {
            return $this->originalAdapter->getUrl($routePath, $routeParams);
        }

        if (!isset($this->cache[$routePath])) {
            $this->cache[$routePath] = $this->getClearUrl($routePath, $routeParams);
        }

        return $this->buildUrl($this->cache[$routePath], $routeParams);
    }

    public function isApplicable(string $routePath = null, array $routeParams = null): bool
    {
        return $this->originalAdapter->isApplicable($routePath, $routeParams);
    }

    private function getClearUrl(string $routePath = null, array $routeParams = null): string
    {
        $routeParams['_query'] = [];
        return $this->originalAdapter->getUrl($routePath, $routeParams);
    }

    private function buildUrl(string $clearUrl, array $routeParams): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $parsedClearUrl = parse_url($clearUrl);

        $newQueryParams = $routeParams['_query'];
        if (isset($parsedClearUrl['query'])) {
            $clearQueryString = $parsedClearUrl['query'];
            $delimiter = strpos($clearQueryString, '&amp;') !== false ? '&amp;' : '&';
            $clearQueryString = str_replace($delimiter, '&', $clearQueryString);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            parse_str($clearQueryString, $clearQueryParams);

            $newQueryParams += $clearQueryParams;
        }

        $newQueryString = http_build_query($newQueryParams);

        if (isset($clearQueryString)) {
            $buildUrl = str_replace($clearQueryString, $newQueryString, $clearUrl);
        } else {
            $buildUrl = $clearUrl . '?' . $newQueryString;
        }

        return trim($buildUrl, '?');
    }
}
