<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api;

/**
 * Provide methods for resolve group data for group with custom position.
 *
 * @api
 */
interface CustomGroupResolverInterface
{
    /**
     * @param int $groupId
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroup(
        int $groupId,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

    /**
     * @param int $groupId
     * @param int $productId
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupForProduct(
        int $groupId,
        int $productId,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

    /**
     * @param int $groupId
     * @param string $maskedCartId
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupForGuestCart(
        int $groupId,
        string $maskedCartId,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

    /**
     * @param int $groupId
     * @param int $quoteId
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupForCart(
        int $groupId,
        int $quoteId,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
}
