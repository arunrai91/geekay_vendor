<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api\Data;

interface OptionSettingInterface
{
    public const DESCRIPTION = 'description';
    public const SHORT_DESCRIPTION = 'short_description';
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const FILTER_CODE = 'filter_code';
    public const STORE_ID = 'store_id';
    public const IMAGE = 'image';
    public const LABEL = 'title';
    public const META_DESCRIPTION = 'meta_description';
    public const META_KEYWORDS = 'meta_keywords';
    public const META_TITLE = 'meta_title';
    public const OPTION_SETTING_ID = 'option_setting_id';
    public const VALUE = 'value';
    public const TITLE = 'title';
    public const TOP_CMS_BLOCK_ID = 'top_cms_block_id';
    public const BOTTOM_CMS_BLOCK_ID = 'bottom_cms_block_id';
    public const IS_FEATURED = 'is_featured';
    public const IS_SHOW_IN_WIDGET = 'is_show_in_widget';
    public const IS_SHOW_IN_SLIDER = 'is_show_in_slider';
    public const SLIDER_POSITION = 'slider_position';
    public const SLIDER_IMAGE = 'slider_image';
    public const IMAGE_ALT = 'image_alt';
    public const SMALL_IMAGE_ALT = 'small_image_alt';
    public const URL_ALIAS = 'url_alias';
    public const BRAND_PAGE_LAYOUT = 'page_layout';
    public const SHOW_BRAND_INFO = 'show_brand_info';
    public const BRAND_INFO_BLOCK_POSITION = 'brand_info_block_position';
    public const BRAND_INFO_POSTAL_ADDRESS = 'brand_info_postal_address';
    public const BRAND_INFO_ELECTRONIC_ADDRESS = 'brand_info_electronic_address';
    public const BRAND_INFO_CONTACT = 'brand_info_contact';

    /**
     * @param bool $shouldParse
     *
     * @return mixed|string
     */
    public function getDescription($shouldParse = false);

    /**
     * @return string
     */
    public function getShortDescription();

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @return bool
     */
    public function getIsFeatured();

    /**
     * @return bool
     */
    public function getIsShowInWidget(): bool;

    /**
     * @return bool
     */
    public function getIsShowInSlider(): bool;

    /**
     * @return string
     */
    public function getAttributeCode(): string;

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @return int
     */
    public function getValue();

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @return int|null
     */
    public function getTopCmsBlockId();

    /**
     * @return int|null
     */
    public function getBottomCmsBlockId();

    /**
     * @return int|null
     */
    public function getSliderPosition();

    /**
     * @return string
     */
    public function getImageAlt(): string;

    /**
     * @return string
     */
    public function getSmallImageAlt();

    /**
     * @param string $description
     * @return OptionSettingInterface
     */
    public function setDescription($description);

    /**
     * @param string $code
     * @return void
     */
    public function setAttributeCode(string $code): void;

    /**
     * @param int $isFeatured
     * @return OptionSettingInterface
     */
    public function setIsFeatured($isFeatured);

    /**
     * @param bool $isShowInWidget
     *
     * @return OptionSettingInterface
     */
    public function setIsShowInWidget(bool $isShowInWidget);

    /**
     * @param bool $isShowInSlider
     *
     * @return OptionSettingInterface
     */
    public function setIsShowInSlider(bool $isShowInSlider): OptionSettingInterface;

    /**
     * @param string $image
     * @return OptionSettingInterface
     */
    public function setImage($image);

    /**
     * @param string $image
     * @return OptionSettingInterface
     */
    public function setSliderImage($image);

    /**
     * @param string $alt
     * @return void
     */
    public function setImageAlt(string $alt): void;

    /**
     * @param string $alt
     * @return OptionSettingInterface
     */
    public function setSmallImageAlt($alt);

    /**
     * @param int $id
     * @return OptionSettingInterface
     */
    public function setId($id);

    /**
     * @param int $id
     * @return OptionSettingInterface
     */
    public function setStoreId($id);

    /**
     * @param int $value
     * @return OptionSettingInterface
     */
    public function setValue($value);

    /**
     * @param string $metaDescription
     * @return OptionSettingInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param string $metaKeywords
     * @return OptionSettingInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @param string $metaTitle
     * @return OptionSettingInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @param string $title
     * @return OptionSettingInterface
     */
    public function setTitle($title);

    /**
     * @param int|null $id
     * @return OptionSettingInterface
     */
    public function setTopCmsBlockId($id);

    /**
     * @param int|null $id
     * @return OptionSettingInterface
     */
    public function setBottomCmsBlockId($id);

    /**
     * @param int $pos
     * @return OptionSettingInterface
     */
    public function setSliderPosition($pos);

    /**
     * @return null|string
     */
    public function getImage();

    /**
     * @return null|string
     */
    public function getSliderImage();

    /**
     * Empty string '' - convert url alias from name.
     * Null - use value from global store. Or if it is global, then same behavior as on empty string.
     * @return string|null
     */
    public function getUrlAlias(): ?string;

    /**
     * @param null|string $urlAlias
     */
    public function setUrlAlias(?string $urlAlias): void;

    /**
     * @return string|null
     */
    public function getPageLayout(): ?string;

    /**
     * @param string $pageLayout
     * @return void
     */
    public function setPageLayout(string $pageLayout): void;

    /**
     * @return bool|null
     */
    public function isShowBrandInfo(): ?bool;

    /**
     * @param bool|null $isShowBrandInfo
     * @return void
     */
    public function setIsShowBrandInfo(?bool $isShowBrandInfo): void;

    /**
     * @return string|null
     */
    public function getBrandInfoBlockPosition(): ?string;

    /**
     * @param string|null $brandInfoBlockPosition
     * @return void
     */
    public function setBrandInfoBlockPosition(?string $brandInfoBlockPosition): void;

    /**
     * @return string|null
     */
    public function getBrandInfoPostalAddress(): ?string;

    /**
     * @param string|null $brandInfoPostalAddress
     * @return void
     */
    public function setBrandInfoPostalAddress(?string $brandInfoPostalAddress): void;

    /**
     * @return string|null
     */
    public function getBrandInfoElectronicAddress(): ?string;

    /**
     * @param string|null $brandInfoElectronicAddress
     * @return void
     */
    public function setBrandInfoElectronicAddress(?string $brandInfoElectronicAddress): void;

    /**
     * @return string|null
     */
    public function getBrandInfoContact(): ?string;

    /**
     * @param string|null $brandInfoContact
     * @return void
     */
    public function setBrandInfoContact(?string $brandInfoContact): void;
}
