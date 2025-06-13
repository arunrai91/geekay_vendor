<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Block\Adminhtml\Buttons;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class GenericButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    public function __construct(
        Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $context->getRequest();
        $this->authorization = $context->getAuthorization();
    }

    public function getUrl($route = '', $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function getButtonData(): array
    {
        return [];
    }
}
