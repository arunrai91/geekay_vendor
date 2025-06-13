<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Request;

class PageTitleRegistry
{
    /**
     * @var string|null
     */
    private ?string $pageTitle = null;

    public function set(?string $pageTitle, bool $graceful = false): void
    {
        if ($this->pageTitle !== null && $graceful) {
            return;
        }

        $this->pageTitle = $pageTitle;
    }

    public function get(): ?string
    {
        return $this->pageTitle;
    }
}
