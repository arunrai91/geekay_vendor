<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Magento\Framework\App\Helper\Context;

class State extends \Magento\Framework\App\Helper\AbstractHelper
{
    private const SHOPBY_EXTRA_PARAM = 'amshopby';

    /**
     * @var UrlBuilderInterface
     */
    private $amUrlBuilder;

    public function __construct(
        Context $context,
        UrlBuilderInterface $amUrlBuilder
    ) {
        parent::__construct($context);
        $this->amUrlBuilder = $amUrlBuilder;
    }

    /**
     * @return mixed
     */
    public function getCurrentUrl()
    {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $this->getQueryParams();
        $result = str_replace('&amp;', '&', $this->amUrlBuilder->getUrl('*/*/*', $params));

        return $result;
    }

    public function getQueryParams(): array
    {
        return [
            '_' => null,
            'shopbyAjax' => null,
            'shopbySorting' => null,
            'price-ranges' => $this->getPriceRangesParam(),
            'dt' => null,
            'df' => null,
            'shopbyCounterAjax' => null
        ];
    }

    private function getPriceRangesParam(): ?string
    {
        if ($this->skipCheckForPriceParam()
            || $this->requestHasAjaxPriceParam()
        ) {
            return $this->_getRequest()->getParam('price-ranges', null);
        }

        return null;
    }

    private function requestHasAjaxPriceParam(): bool
    {
        return (bool)$this->_getRequest()->getParam('shopbyAjax')
            && !empty($this->_getRequest()->getParam(self::SHOPBY_EXTRA_PARAM, [])['price'] ?? '');
    }

    /**
     * For sorting actions (pager, sort, etc) ignore skip price-ranges parameter.
     */
    private function skipCheckForPriceParam(): bool
    {
        return (bool)$this->_getRequest()->getParam('shopbySorting');
    }
}
