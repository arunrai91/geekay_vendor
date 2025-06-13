<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\UrlResolver;

interface UrlResolverInterface
{
    /**
     * Resolve an url
     *
     * @return string
     */
    public function resolve(): string;
}
