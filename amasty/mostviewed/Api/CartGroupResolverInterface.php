<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api;

/**
 * Provide methods for resolve group data for cart.
 *
 * @api
 */
interface CartGroupResolverInterface
{
    /**
     * @param string $maskedCartId
     * @param string $position
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupByMaskedQuoteIdAndPosition(
        string $maskedCartId,
        string $position,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

    /**
     * @param int $quoteId
     * @param string $position
     * @param int|null $customerGroupId
     * @return \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface
     */
    public function getGroupByQuoteIdAndPosition(
        int $quoteId,
        string $position,
        ?int $customerGroupId = null
    ): \Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
}
