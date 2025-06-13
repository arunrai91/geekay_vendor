<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\Url;

use Amasty\Shopby\Model\Request as ShopbyRequest;
use Amasty\Shopby\Model\Resolver as ShopbyResolver;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\Url\QueryParamsResolverInterface;

class RouteParamsResolver
{
    /**
     * @var ShopbyResolver
     */
    private ShopbyResolver $amResolver;

    /**
     * @var LayerResolver
     */
    private LayerResolver $layerResolver;

    /**
     * @var Layer|null
     */
    private ?Layer $layer = null;

    /**
     * @var QueryParamsResolverInterface
     */
    private QueryParamsResolverInterface $queryParamsResolver;

    /**
     * @var ShopbyRequest
     */
    private ShopbyRequest $shopbyRequest;

    public function __construct(
        LayerResolver $layerResolver,
        ShopbyResolver $amResolver,
        QueryParamsResolverInterface $queryParamsResolver,
        ShopbyRequest $shopbyRequest
    ) {
        $this->amResolver = $amResolver;
        $this->layerResolver = $layerResolver;
        $this->queryParamsResolver = $queryParamsResolver;
        $this->shopbyRequest = $shopbyRequest;
    }

    /**
     * @param \Magento\Framework\Url\RouteParamsResolver $subject
     * @param \Closure $proceed
     * @param array $data
     * @param bool|true $unsetOldParams
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundSetRouteParams(
        \Magento\Framework\Url\RouteParamsResolver $subject,
        \Closure $proceed,
        array $data,
        $unsetOldParams = true
    ) {
        if (!array_key_exists('_current', $data)) {
            return $proceed($data, $unsetOldParams);
        }

        $queryParams = $this->queryParamsResolver->getQueryParams();

        $filters = $this->getLayer()->getState()->getFilters();
        foreach ($filters as $filter) {
            $filterParam = $this->shopbyRequest->getFilterParam($filter->getFilter());
            if (!empty($filterParam)) {
                $queryParams[$filter->getFilter()->getRequestVar()] = $filterParam;
            }
        }

        $queryParams[\Amasty\Shopby\Block\Navigation\UrlModifier::VAR_REPLACE_URL] = null;
        $queryParams['amshopby'] = null;

        if (array_key_exists('price', $queryParams)) {
            $data['price'] = null; //fix for catalogsearxch pages
        }

        $result = $proceed($data, $unsetOldParams);
        $this->queryParamsResolver->addQueryParams($queryParams);

        return $result;
    }

    /**
     * @return Layer
     */
    private function getLayer()
    {
        if (!$this->layer) {
            $this->layer = $this->amResolver->loadFromParent($this->layerResolver)->get();
        }

        return $this->layer;
    }
}
