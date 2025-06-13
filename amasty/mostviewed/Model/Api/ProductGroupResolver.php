<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Api\ProductGroupResolverInterface;
use Amasty\Mostviewed\Model\Api\Group\ByPosition\GetGroupByProductIdAndPosition;
use Amasty\Mostviewed\Model\Api\Group\ByPosition\GetGroupByProductSkuAndPosition;
use Amasty\Mostviewed\Model\Customer\CustomerGroupContext;

class ProductGroupResolver implements ProductGroupResolverInterface
{
    /**
     * @var CustomerGroupContext
     */
    private $customerGroupContext;

    /**
     * @var GetGroupByProductIdAndPosition
     */
    private $getGroupByProductIdAndPosition;

    /**
     * @var GetGroupByProductSkuAndPosition
     */
    private $getGroupByProductSkuAndPosition;

    public function __construct(
        CustomerGroupContext $customerGroupContext,
        GetGroupByProductIdAndPosition $getGroupByProductIdAndPosition,
        GetGroupByProductSkuAndPosition $getGroupByProductSkuAndPosition
    ) {
        $this->customerGroupContext = $customerGroupContext;
        $this->getGroupByProductIdAndPosition = $getGroupByProductIdAndPosition;
        $this->getGroupByProductSkuAndPosition = $getGroupByProductSkuAndPosition;
    }

    public function getGroupByProductIdAndPosition(
        int $productId,
        string $position,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $this->customerGroupContext->set($customerGroupId);
        return $this->getGroupByProductIdAndPosition->execute($productId, $position);
    }

    public function getGroupByProductSkuAndPosition(
        string $productSku,
        string $position,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $this->customerGroupContext->set($customerGroupId);
        return $this->getGroupByProductSkuAndPosition->execute($productSku, $position);
    }
}
