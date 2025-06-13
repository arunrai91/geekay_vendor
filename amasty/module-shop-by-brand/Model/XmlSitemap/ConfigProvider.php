<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\XmlSitemap;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    public const BRAND_HREFLANG = 'hreflang/brand_hreflang';

    /**
     * @var string
     */
    protected $pathPrefix = 'amxmlsitemap/';

    public function isBrandHreflang(): bool
    {
        return (bool) $this->getValue(self::BRAND_HREFLANG);
    }
}
