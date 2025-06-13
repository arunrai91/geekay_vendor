<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\UrlParser;

use Amasty\ShopbyBrand\Helper\Data as BrandHelper;
use Amasty\ShopbyBrand\Model\ConfigProvider;

class MatchBrandParams
{
    /**
     * @var BrandHelper
     */
    private $brandHelper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(BrandHelper $brandHelper, ConfigProvider $configProvider)
    {
        $this->brandHelper = $brandHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @param string $identifier
     * @return array
     */
    public function execute($identifier)
    {
        $identifier = $this->cutBrandIdentifier($identifier);
        $aliases = $this->brandHelper->getBrandAliases();

        foreach ($aliases as $optionId => $alias) {
            if (!strcasecmp($alias, $identifier)) {
                return [$this->configProvider->getBrandAttributeCode() => $optionId];
            }
        }

        return [];
    }

    /**
     * @param string $identifier
     * @return string
     */
    private function cutBrandIdentifier($identifier)
    {
        $brandPageUrlKey = $this->brandHelper->getBrandUrlKey();
        $identifier = trim($identifier, '/');

        if (!empty($brandPageUrlKey) && strpos($identifier, $brandPageUrlKey . '/') === 0) {
            $identifier = ltrim(substr($identifier, strlen($brandPageUrlKey . '/')), '/');
        }

        return $identifier;
    }
}
