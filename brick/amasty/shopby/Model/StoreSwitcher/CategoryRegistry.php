<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\StoreSwitcher;

class CategoryRegistry
{
    /**
     * @var int|null
     */
    private $categoryId;

    public function set(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function get(): ?int
    {
        return $this->categoryId;
    }
}
