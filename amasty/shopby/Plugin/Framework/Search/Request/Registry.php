<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Framework\Search\Request;

/**
 * Mark registry for Category Counter Request.
 *
 * Using external flag registry because request builder doest allow edit request (can't set flag to array).
 */
class Registry
{
    /**
     * @var bool
     */
    private $isCategoryRequest = false;

    public function isAdditionalCleaningAllowed(): bool
    {
        return $this->isCategoryRequest;
    }

    public function setAdditionalCleaningAllowed(bool $isAllowed): void
    {
        $this->isCategoryRequest = $isAllowed;
    }
}
