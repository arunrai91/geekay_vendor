<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Utils;

use Amasty\ShopbySeo\Helper\Config;

class AliasesDelimiterProvider
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve seo options delimiter
     *
     * @return string
     */
    public function execute()
    {
        $delimiter = $this->config->getOptionSeparator() ?: '-';
        return (string) $delimiter;
    }
}
