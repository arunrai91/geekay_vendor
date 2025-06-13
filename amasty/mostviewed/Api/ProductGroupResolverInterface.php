<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api;

/**
 * Provide methods for resolve group data for product.
 *
 * @api
 */
interface ProductGroupResolverInterface
{
    /**
     * @param int $productId
     * @param string $position
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupByProductIdAndPosition(
        int $productId,
        string $position,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

    /**
     * @param string $productSku
     * @param string $position
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupByProductSkuAndPosition(
        string $productSku,
        string $position,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
}
