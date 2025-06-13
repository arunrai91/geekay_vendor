<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute;

interface ParserInterface
{
    /**
     * Parse prepared aliases and update request
     *
     * @param array $aliases
     * @param string $seoPart
     * @return array
     */
    public function parse(array $aliases, string $seoPart): array;
}
