<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\UrlParser\Url;

use Amasty\ShopbySeo\Model\ConfigProvider;

class SuffixManager
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function removeSuffix(string $identifier): string
    {
        $seoSuffix = $this->configProvider->getSeoSuffix();
        if (strpos($seoSuffix, '/') === false) {
            $identifier = rtrim($identifier, '/');
        }

        if (trim($identifier, '/') && $seoSuffix) {
            $suffixPosition = strrpos($identifier, $seoSuffix);

            if ($suffixPosition !== false
                && ($suffixPosition == strlen($identifier) - strlen($seoSuffix))
            ) {
                $identifier = substr($identifier, 0, $suffixPosition);
            }
        }

        return $identifier;
    }

    public function addSuffix(string $identifier): string
    {
        return $identifier . $this->configProvider->getSeoSuffix();
    }
}
