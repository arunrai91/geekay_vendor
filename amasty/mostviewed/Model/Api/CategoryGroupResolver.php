<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api;

use Amasty\Mostviewed\Api\CategoryGroupResolverInterface;
use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;
use Amasty\Mostviewed\Model\Api\Group\ByPosition\GetGroupByCategoryIdAndPosition;
use Amasty\Mostviewed\Model\Customer\CustomerGroupContext;

class CategoryGroupResolver implements CategoryGroupResolverInterface
{
    /**
     * @var CustomerGroupContext
     */
    private $customerGroupContext;

    /**
     * @var GetGroupByCategoryIdAndPosition
     */
    private $getGroupByCategoryIdAndPosition;

    public function __construct(
        CustomerGroupContext $customerGroupContext,
        GetGroupByCategoryIdAndPosition $getGroupByCategoryIdAndPosition
    ) {
        $this->customerGroupContext = $customerGroupContext;
        $this->getGroupByCategoryIdAndPosition = $getGroupByCategoryIdAndPosition;
    }

    public function getGroupByCategoryIdAndPosition(
        int $categoryId,
        string $position,
        ?int $customerGroupId = null
    ): GroupProductsResultInterface {
        $this->customerGroupContext->set($customerGroupId);
        return $this->getGroupByCategoryIdAndPosition->execute($categoryId, $position);
    }
}
