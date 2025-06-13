<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\Request;

class RobotsRegistry
{
    /**
     * @var string|null
     */
    private ?string $robots = null;

    public function get(): ?string
    {
        return $this->robots;
    }

    public function set(?string $robots): void
    {
        $this->robots = $robots;
    }

    public function _resetState(): void
    {
        $this->robots = null;
    }
}
