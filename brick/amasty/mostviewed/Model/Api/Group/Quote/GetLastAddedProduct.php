<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\Quote;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Api\Data\CartInterface;

class GetLastAddedProduct
{
    public function execute(CartInterface $quote): ?ProductInterface
    {
        $items = $quote->getAllVisibleItems();
        if (!empty($items)) {
            $result = array_reverse($items);
            $product = array_key_exists(0, $result) ? $result[0]->getProduct() : null;
        } else {
            $product = null;
        }

        return $product;
    }
}
