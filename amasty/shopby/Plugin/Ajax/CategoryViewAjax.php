<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Ajax;

use Amasty\Shopby\Model\Ajax\AjaxResponseBuilder;
use Amasty\Shopby\Model\Ajax\Counter\CounterDataProvider;
use Amasty\Shopby\Model\Ajax\RequestResponseUtils;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\View\Result\Page;

class CategoryViewAjax
{
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var RequestResponseUtils
     */
    private $utils;

    /**
     * @var AjaxResponseBuilder
     */
    private $ajaxResponseBuilder;

    /**
     * @var CounterDataProvider
     */
    private $counterDataProvider;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    public function __construct(
        ActionFlag $actionFlag,
        RequestResponseUtils $utils,
        AjaxResponseBuilder $ajaxResponseBuilder,
        CounterDataProvider $counterDataProvider,
        RequestInterface $request
    ) {
        $this->actionFlag = $actionFlag;
        $this->utils = $utils;
        $this->ajaxResponseBuilder = $ajaxResponseBuilder;
        $this->counterDataProvider = $counterDataProvider;
        $this->request = $request;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $controller): void
    {
        if ($this->utils->isAjaxNavigation($this->request)) {
            $this->actionFlag->set('', 'no-renderLayout', true);
        }
    }

    /**
     * @param ActionInterface $controller
     * @param Page $page
     *
     * @return Raw|Page
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(ActionInterface $controller, $page)
    {
        if (!$this->utils->isAjaxNavigation($this->request)) {
            return $page;
        }

        $responseData = $this->utils->isCounterRequest($this->request)
            ? $this->counterDataProvider->execute()
            : $this->ajaxResponseBuilder->build();

        return $this->utils->prepareResponse($responseData);
    }
}
