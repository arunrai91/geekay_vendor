<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Ajax\Counter;

use Amasty\Shopby\Helper\State as StateHelper;
use Amasty\Shopby\Model\ConfigProvider;
use LogicException;
use Magento\Framework\View\LayoutInterface;

class CounterDataProvider
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var StateHelper
     */
    private $stateHelper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(LayoutInterface $layout, StateHelper $stateHelper, ConfigProvider $configProvider)
    {
        $this->layout = $layout;
        $this->stateHelper = $stateHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     * @throws LogicException
     */
    public function execute(): array
    {
        /** @var \Magento\Framework\View\Element\Template $products */
        $products = $this->layout->getBlock('category.products');
        if (!$products) {
            $products = $this->layout->getBlock('search.result');
        }

        $productList = $products->getChildBlock('product_list') ?: $products->getChildBlock('search_result_list');
        if (!$productList) {
            throw new LogicException('Unable to find product_list or search_result_list blocks');
        }

        $responseData['productsCount'] = $productList->getLoadedProductCollection()->getSize();
        if (!$this->configProvider->isAjaxEnabled()) {
            $responseData['url'] = $this->stateHelper->getCurrentUrl();
        }

        return $responseData;
    }
}
