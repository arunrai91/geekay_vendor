<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface BannerInterface extends ExtensibleDataInterface
{
    public const EXTENSION_CODE = 'promo_banners_lite';

    public const TOP_BANNER = 0;
    public const AFTER_BANNER = 1;
    public const PRODUCT_LABEL = 2;

    /**
     * Array with banner positions
     */
    public const BANNER_POSITIONS = [
        self::TOP_BANNER => 'top',
        self::AFTER_BANNER => 'after_description',
        self::PRODUCT_LABEL => 'product'
    ];

    /**#@+
     * Constants defined for keys of data array
     */
    public const ENTITY_ID = 'entity_id';
    public const SALESRULE_ID = 'salesrule_id';
    public const BANNER_TYPE = 'banner_type';
    public const BANNER_IMAGE = 'banner_image';
    public const BANNER_ALT = 'banner_alt';
    public const BANNER_HOVER_TEXT = 'banner_hover_text';
    public const BANNER_LINK = 'banner_link';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getSalesruleId();

    /**
     * @param int $salesruleId
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setSalesruleId($salesruleId);

    /**
     * @return string|null
     */
    public function getBannerType();

    /**
     * @param string|null $bannerType
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setBannerType($bannerType);

    /**
     * @return string|null
     */
    public function getBannerImage();

    /**
     * @param string|null $bannerImage
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setBannerImage($bannerImage);

    /**
     * @return string|null
     */
    public function getBannerAlt();

    /**
     * @param string|null $bannerAlt
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setBannerAlt($bannerAlt);

    /**
     * @return string|null
     */
    public function getBannerHoverText();

    /**
     * @param string|null $bannerHoverText
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setBannerHoverText($bannerHoverText);

    /**
     * @return string|null
     */
    public function getBannerLink();

    /**
     * @param string|null $bannerLink
     *
     * @return \Amasty\BannersLite\Api\Data\BannerInterface
     */
    public function setBannerLink($bannerLink);
}
