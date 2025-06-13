<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Ajax;

use Amasty\Shopby\Model\Config\MobileConfigResolver;
use Amasty\Shopby\Model\ConfigProvider;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw as RawResponce;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Serialize\Serializer\Json;

class RequestResponseUtils
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var MobileConfigResolver
     */
    private $configResolver;

    public function __construct(MobileConfigResolver $configResolver, RawFactory $resultRawFactory, Json $serializer)
    {
        $this->resultRawFactory = $resultRawFactory;
        $this->serializer = $serializer;
        $this->configResolver = $configResolver;
    }

    /**
     * Is Request are for feature "improved layered navigation with AJAX"?
     */
    public function isAjaxNavigation(RequestInterface $request): bool
    {
        if (!$request instanceof Http) {
            return false;
        }
        $isAjax = $request->isXmlHttpRequest() && $request->isAjax() && $request->getParam('shopbyAjax', false);
        $isScroll = $request->getParam('is_scroll');
        return $this->configResolver->isAjaxEnabled() && $isAjax && !$isScroll;
    }

    public function prepareResponse(array $data): RawResponce
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        if (isset($data['tags'])) {
            $response->setHeader('X-Magento-Tags', $data['tags']);
            unset($data['tags']);
        }

        $response->setContents($this->serializer->serialize($data));
        return $response;
    }

    public function isCounterRequest(RequestInterface $request): bool
    {
        if (!$request instanceof Http) {
            return false;
        }

        return (bool)$request->getParam('shopbyCounterAjax', false);
    }
}
