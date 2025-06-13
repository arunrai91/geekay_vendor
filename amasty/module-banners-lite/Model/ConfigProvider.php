<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_banners_lite/';

    public const BANNERS_GROUP_GENERAL = 'general/';
    public const ENABLE_TOP_BANNERS = 'enable_top_banner';
    public const ENABLE_AFTER_BANNERS = 'enable_after_banner';
    public const ENABLE_PRODUCT_BANNERS = 'enable_product_label';
    public const ONE_BANNER = 'show_one_banner';

    /**
     * @return bool
     */
    public function isBannersEnabled()
    {
        return $this->isTopBannersEnabled() || $this->isAfterBannersEnabled() || $this->isProductBannersEnabled();
    }

    /**
     * @return bool
     */
    public function isTopBannersEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ENABLE_TOP_BANNERS);
    }

    /**
     * @return bool
     */
    public function isAfterBannersEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ENABLE_AFTER_BANNERS);
    }

    /**
     * @return bool
     */
    public function isProductBannersEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ENABLE_PRODUCT_BANNERS);
    }

    /**
     * @return bool
     */
    public function isOneBannerEnabled()
    {
        return (bool)$this->getValue(self::BANNERS_GROUP_GENERAL . self::ONE_BANNER);
    }
}
