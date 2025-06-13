<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\UrlBuilder;

use Amasty\ShopbyBase\Api\UrlModifierInterface;

class UrlModifier
{
    /**
     * @var UrlModifierInterface[]
     */
    private $urlModifiers;

    public function __construct(array $urlModifiers = [])
    {
        $this->initModifiers($urlModifiers);
    }

    public function execute(string $url, ?int $categoryId = null, bool $skipModuleCheck = false): string
    {
        foreach ($this->urlModifiers as $modifier) {
            $url = $modifier->modifyUrl($url, $categoryId, $skipModuleCheck);
        }
        return $url;
    }

    private function initModifiers(array $urlModifiers = []): void
    {
        foreach ($urlModifiers as $urlModifier) {
            if ($urlModifier instanceof UrlModifierInterface) {
                $this->urlModifiers[] = $urlModifier;
            }
        }
    }
}
