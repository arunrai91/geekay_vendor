<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\UrlBuilder;

class Adapter implements \Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     * @return string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        return $this->urlBuilder->getUrl($routePath, $routeParams);
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }

    /**
     * @param $params
     * @return string
     */
    public function getBaseUrl($params)
    {
        return $this->urlBuilder->getBaseUrl($params);
    }

    public function isApplicable(?string $routePath = null, ?array $routeParams = null): bool
    {
        return true;
    }
}
