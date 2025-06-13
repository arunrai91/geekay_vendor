<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Request;

class IsAllProductsRegistry
{
    /**
     * @var bool|null
     */
    private ?bool $isAllProducts = null;

    public function setIsAllProducts(bool $isAllProducts): void
    {
        $this->isAllProducts = $isAllProducts;
    }

    public function isAllProducts(): bool
    {
        return (bool)$this->isAllProducts;
    }

    public function _resetState(): void
    {
        $this->isAllProducts = null;
    }
}
