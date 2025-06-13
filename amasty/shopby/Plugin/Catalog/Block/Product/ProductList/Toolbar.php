<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Catalog\Block\Product\ProductList;

class Toolbar
{
    /**
     * @var \Amasty\ShopbyBase\Model\UrlBuilder
     */
    private $urlBuilder;

    public function __construct(\Amasty\ShopbyBase\Model\UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param array $params
     * @return mixed
     */
    public function aroundGetPagerUrl($subject, callable $proceed, $params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->urlBuilder->getUrl('*/*/*', $urlParams);
    }
}
