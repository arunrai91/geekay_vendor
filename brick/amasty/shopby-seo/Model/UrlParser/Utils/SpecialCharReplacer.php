<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils;

use Amasty\ShopbySeo\Model\ConfigProvider;

class SpecialCharReplacer
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Replace special chars with config symbol
     *
     * @param string $value
     * @return string
     */
    public function replace(string $value): string
    {
        return str_replace(
            $this->configProvider->getOptionSeparator(),
            $this->configProvider->getSpecialChar(),
            $value
        );
    }

    public function normalizeAttributeCode(string $value): string
    {
        return str_replace('-', '_', $value);
    }
}
