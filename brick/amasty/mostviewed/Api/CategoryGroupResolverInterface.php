<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api;

/**
 * Provide methods for resolve group data for category.
 *
 * @api
 */
interface CategoryGroupResolverInterface
{
    /**
     * @param int $categoryId
     * @param string $position
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupByCategoryIdAndPosition(
        int $categoryId,
        string $position,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
}
