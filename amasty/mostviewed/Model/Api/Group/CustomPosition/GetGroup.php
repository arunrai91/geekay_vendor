<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Api\Group\CustomPosition;

use Amasty\Mostviewed\Api\Data\GroupProductsResultInterface;

class GetGroup
{
    /**
     * @var ResolveGroupProductsResult
     */
    private $resolveGroupProductsResult;

    public function __construct(ResolveGroupProductsResult $resolveGroupProductsResult)
    {
        $this->resolveGroupProductsResult = $resolveGroupProductsResult;
    }

    public function execute(int $groupId): GroupProductsResultInterface
    {
        return $this->resolveGroupProductsResult->execute($groupId);
    }
}
