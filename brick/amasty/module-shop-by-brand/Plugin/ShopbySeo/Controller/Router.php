<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Controller;

use Magento\Framework\App\RequestInterface;

class Router
{
    public const SINGLE_PARAM = 1;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    public function __construct(\Amasty\ShopbyBrand\Helper\Data $brandHelper)
    {
        $this->brandHelper = $brandHelper;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @param $identifier
     * @param $params
     */
    public function aroundModifyRequest(
        $subject,
        callable $proceed,
        RequestInterface $request,
        $identifier,
        $params
    ) {
        $brandAttributeCode = $this->brandHelper->getBrandAttributeCode();
        $brandUrlKey = $this->brandHelper->getBrandUrlKey();
        if (count($params) == self::SINGLE_PARAM
            && isset($params[$brandAttributeCode])
            && trim($identifier, '/') == $brandUrlKey
        ) {
            return $subject;
        }

        return $proceed($request, $identifier, $params);
    }
}
