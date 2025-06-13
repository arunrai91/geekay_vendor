<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Ajax;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\Request\Http;

class ProductListWrapper
{
    /**
     * @var Http
     */
    private $request;

    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    public function afterToHtml(ListProduct $subject, string $result): string
    {
        if ($subject->getNameInLayout() !== 'category.products.list'
            && $subject->getNameInLayout() !== 'search_result_list'
        ) {
            return $result;
        }

        if ($this->request->getParam('is_scroll')) {
            return $result;
        }

        return sprintf('<div id="amasty-shopby-product-list">%s</div>', $result);
    }
}
