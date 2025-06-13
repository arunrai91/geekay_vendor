<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\Product;

use Amasty\Mostviewed\Model\ResourceModel\Product\Collection as ProductCollection;
use Amasty\Mostviewed\Model\ResourceModel\Product\Collection\LoadProductIds;

/**
 * Retrieve product ids from collection for current page.
 * Presave order from collection in result.
 */
class GetProductIds
{
    /**
     * @var LoadProductIds
     */
    private $loadProductIds;

    public function __construct(LoadProductIds $loadProductIds)
    {
        $this->loadProductIds = $loadProductIds;
    }

    /**
     * @return int[]
     */
    public function execute(ProductCollection $productCollection): array
    {
        return array_map(function ($productId) {
            return (int)$productId;
        }, $this->loadProductIds->execute($productCollection));
    }
}
