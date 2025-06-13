<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Page for Magento 2 (System)
 */

namespace Amasty\ShopbyPage\Model\Request\Page;

use Amasty\ShopbyPage\Api\Data\PageInterface;

class Registry
{
    /**
     * @var PageInterface|null
     */
    private ?PageInterface $page = null;

    public function set(?PageInterface $page): void
    {
        $this->page = $page;
    }

    public function get(): ?PageInterface
    {
        return $this->page;
    }

    public function _resetState(): void
    {
        $this->page = null;
    }
}
