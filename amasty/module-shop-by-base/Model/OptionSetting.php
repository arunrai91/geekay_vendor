<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBase\Model\OptionSettings\ImageFileResolver;
use Amasty\ShopbyBase\Model\OptionSettings\UrlResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Widget\Model\Template\Filter as WidgetFilter;

class OptionSetting extends \Magento\Framework\Model\AbstractModel implements OptionSettingInterface, IdentityInterface
{
    public const CACHE_TAG = 'amshopby_option_setting_v';
    public const IMAGES_DIR = '/amasty/shopby/option_images/';
    public const SLIDER_DIR = 'slider/';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amshopby_option_setting';

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var WidgetFilter
     */
    private $filter;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AttributeRepository $attributeRepository,
        WidgetFilter $filter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->filter = $filter;
    }

    /**
     * Protected OptionSetting constructor
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyBase\Model\ResourceModel\OptionSetting::class);
    }

    /**
     * @param bool $shouldParse
     *
     * @return mixed|string
     */
    public function getDescription($shouldParse = false)
    {
        $description = $this->getData(self::DESCRIPTION);

        return $shouldParse ? $this->parseWysiwyg($description) : $description;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::OPTION_SETTING_ID);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @return bool
     */
    public function getIsFeatured()
    {
        return (bool) $this->getData(self::IS_FEATURED);
    }

    public function getIsShowInWidget(): bool
    {
        return (bool) $this->getData(self::IS_SHOW_IN_WIDGET);
    }

    public function getIsShowInSlider(): bool
    {
        return (bool) $this->getData(self::IS_SHOW_IN_SLIDER);
    }

    public function getAttributeCode(): string
    {
        return (string) $this->getDataByKey(self::ATTRIBUTE_CODE);
    }

    /**
     * @return string
     * @deprecated use getAttributeCode without prefix
     */
    public function getFilterCode()
    {
        return $this->getData(self::FILTER_CODE);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getValue()];
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return string
     */
    public function getTopCmsBlockId()
    {
        return $this->getData(self::TOP_CMS_BLOCK_ID);
    }

    /**
     * @return string
     */
    public function getBottomCmsBlockId()
    {
        return $this->getData(self::BOTTOM_CMS_BLOCK_ID);
    }

    /**
     * @return string
     */
    public function getSliderPosition()
    {
        return $this->getData(self::SLIDER_POSITION);
    }

    /**
     * @return string
     */
    public function getSmallImageAlt()
    {
        return $this->getData(self::SMALL_IMAGE_ALT);
    }

    /**
     * @param string $description
     * @return OptionSetting
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @param string $metaDescription
     * @return OptionSetting
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @param string $metaKeywords
     * @return OptionSetting
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * @param string $metaTitle
     * @return OptionSetting
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @param int|mixed $id
     * @return OptionSetting
     */
    public function setId($id)
    {
        return $this->setData(self::OPTION_SETTING_ID, $id);
    }

    /**
     * @param int $id
     * @return OptionSetting
     */
    public function setStoreId($id)
    {
        return $this->setData(self::STORE_ID, $id);
    }

    /**
     * @param int $isFeatured
     * @return OptionSetting
     */
    public function setIsFeatured($isFeatured)
    {
        return $this->setData(self::IS_FEATURED, $isFeatured);
    }

    public function setIsShowInWidget(bool $isShowInWidget)
    {
        return $this->setData(self::IS_SHOW_IN_WIDGET, $isShowInWidget);
    }

    public function setIsShowInSlider(bool $isShowInSlider): OptionSettingInterface
    {
        return $this->setData(self::IS_SHOW_IN_SLIDER, $isShowInSlider);
    }

    /**
     * @param string $image
     * @return OptionSetting
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @param string $image
     * @return OptionSetting
     */
    public function setSliderImage($image)
    {
        return $this->setData(self::SLIDER_IMAGE, $image);
    }

    /**
     * @param string $alt
     * @return OptionSetting
     */
    public function setSmallImageAlt($alt)
    {
        return $this->setData(self::SMALL_IMAGE_ALT, $alt);
    }

    public function setAttributeCode(string $code): void
    {
        $this->setData(self::ATTRIBUTE_CODE, $code);
    }

    /**
     * @param string $filterCode
     * @return OptionSetting
     * @deprecated use setAttributeCode without prefix
     */
    public function setFilterCode($filterCode)
    {
        return $this->setData(self::FILTER_CODE, $filterCode);
    }

    /**
     * @param int $value
     * @return OptionSetting
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @param string $title
     * @return OptionSetting
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param int|null $id
     * @return OptionSetting
     */
    public function setTopCmsBlockId($id)
    {
        return $this->setData(self::TOP_CMS_BLOCK_ID, $id);
    }

    /**
     * @param int|null $id
     * @return OptionSetting
     */
    public function setBottomCmsBlockId($id)
    {
        return $this->setData(self::BOTTOM_CMS_BLOCK_ID, $id);
    }

    /**
     * @param int $pos
     * @return OptionSetting
     */
    public function setSliderPosition($pos)
    {
        return $this->setData(self::SLIDER_POSITION, $pos);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @return string
     */
    public function getSliderImage()
    {
        return $this->getData(self::SLIDER_IMAGE);
    }

    /**
     * @return string
     */
    public function getImageAlt(): string
    {
        return (string) $this->getDataByKey(self::IMAGE_ALT);
    }

    /**
     * @param string $imageAlt
     */
    public function setImageAlt(string $imageAlt): void
    {
        $this->setData(self::IMAGE_ALT, $imageAlt);
    }

    public function getPageLayout(): ?string
    {
        return $this->getDataByKey(self::BRAND_PAGE_LAYOUT);
    }

    public function setPageLayout(string $pageLayout): void
    {
        $this->setData(self::BRAND_PAGE_LAYOUT, $pageLayout);
    }

    public function isShowBrandInfo(): ?bool
    {
        $isShowBrandInfo = $this->getDataByKey(self::SHOW_BRAND_INFO);

        return $isShowBrandInfo !== null ? (bool)$isShowBrandInfo : $isShowBrandInfo;
    }

    public function setIsShowBrandInfo(?bool $isShowBrandInfo): void
    {
        $this->setData(self::SHOW_BRAND_INFO, $isShowBrandInfo);
    }

    public function getBrandInfoBlockPosition(): ?string
    {
        return $this->getDataByKey(self::BRAND_INFO_BLOCK_POSITION);
    }

    public function setBrandInfoBlockPosition(?string $brandInfoBlockPosition): void
    {
        $this->setData(self::BRAND_INFO_BLOCK_POSITION, $brandInfoBlockPosition);
    }

    public function getBrandInfoPostalAddress(): ?string
    {
        return $this->getData(self::BRAND_INFO_POSTAL_ADDRESS);
    }

    public function setBrandInfoPostalAddress(?string $brandInfoPostalAddress): void
    {
        $this->setData(self::BRAND_INFO_POSTAL_ADDRESS, $brandInfoPostalAddress);
    }

    public function getBrandInfoElectronicAddress(): ?string
    {
        return $this->getDataByKey(self::BRAND_INFO_ELECTRONIC_ADDRESS);
    }

    public function setBrandInfoElectronicAddress(?string $brandInfoElectronicAddress): void
    {
        $this->setData(self::BRAND_INFO_ELECTRONIC_ADDRESS, $brandInfoElectronicAddress);
    }

    public function getBrandInfoContact(): ?string
    {
        return $this->getDataByKey(self::BRAND_INFO_CONTACT);
    }

    public function setBrandInfoContact(?string $brandInfoContact): void
    {
        $this->setData(self::BRAND_INFO_CONTACT);
    }

    /**
     * @param string|array  $key
     * @param mixed         $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($key == self::SLIDER_POSITION && $value !== null) {
            $value = max(0, (int)$value);
        }

        return parent::setData($key, $value);
    }

    /**
     * @param int $fileId
     * @param bool $isSlider
     * @return string
     * @deprecared DataModel should have only simple setters and getters
     * @see \Amasty\ShopbyBase\Model\OptionSettings\ImageFileResolver
     */
    public function uploadImage($fileId, $isSlider = false)
    {
        if ($isSlider) {
            return ObjectManager::getInstance()->get(ImageFileResolver::class)
                ->resolveImageSliderUpload($this, $fileId);
        }

        return ObjectManager::getInstance()->get(ImageFileResolver::class)->resolveImageUpload($this, $fileId);
    }

    /**
     * @param bool $isSlider
     * @return void
     * @deprecared DataModel should have only simple setters and getters
     * @see \Amasty\ShopbyBase\Model\OptionSettings\ImageFileResolver
     */
    public function removeImage($isSlider = false)
    {
        if ($isSlider) {
            return ObjectManager::getInstance()->get(ImageFileResolver::class)->resolveRemoveSliderImage($this);
        }

        return ObjectManager::getInstance()->get(ImageFileResolver::class)->resolveRemoveImage($this);
    }

    /**
     * @param bool $isSlider
     * @return string
     * @deprecared DataModel should have only simple setters and getters
     */
    public function getImagePath($isSlider = false)
    {
        if ($isSlider) {
            return ObjectManager::getInstance()->get(ImageFileResolver::class)->resolveSliderImagePath($this);
        }

        return ObjectManager::getInstance()->get(ImageFileResolver::class)->resolveImagePath($this);
    }

    /**
     * @return null|string
     * @deprecared DataModel should have only simple setters and getters
     * @see \Amasty\ShopbyBase\Model\OptionSettings\UrlResolver::resolveImageUrl
     */
    public function getImageUrl()
    {
        return ObjectManager::getInstance()->get(UrlResolver::class)->resolveImageUrl($this);
    }

    /**
     * @param bool $strict
     * @return null|string
     * @deprecared DataModel should have only simple setters and getters
     * @see \Amasty\ShopbyBase\Model\OptionSettings\UrlResolver::resolveSliderImageUrl
     */
    public function getSliderImageUrl($strict = false)
    {
        return ObjectManager::getInstance()->get(UrlResolver::class)->resolveSliderImageUrl($this, $strict);
    }

    /**
     * @return string
     * @deprecared DataModel should have only setters and getters
     * @see \Amasty\ShopbyBase\Model\OptionSettings\UrlResolver::getMediaBaseUrl
     */
    public function getMediaBaseUrl()
    {
        return ObjectManager::getInstance()->get(UrlResolver::class)->getMediaBaseUrl();
    }

    /**
     * Wrapper for repository method
     *
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     * @deprecared DataModel should have only setters and getters
     * @see \Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface::getByCode
     */
    public function getByParams($filterCode, $optionId, $storeId)
    {
        return ObjectManager::getInstance()->get(OptionSettingRepositoryInterface::class)
            ->getByCode(FilterSetting::convertToAttributeCode($filterCode), $optionId, $storeId);
    }

    /**
     * @return string
     * @deprecared DataModel should have only setters and getters
     * @see \Amasty\ShopbyBase\Model\OptionSettings\UrlResolver::resolveBrandUrl
     */
    public function getUrlPath()
    {
        return ObjectManager::getInstance()->get(UrlResolver::class)->resolveBrandUrl($this);
    }

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @param array $data
     * @return OptionSettingInterface|\Magento\Framework\DataObject
     */
    public function saveData($attributeCode, $optionId, $storeId, $data)
    {
        return ObjectManager::getInstance()->get(\Amasty\ShopbyBase\Model\OptionSettings\Save::class)
            ->saveData($attributeCode, (int) $optionId, (int) $storeId, $data);
    }

    /**
     * Empty string '' - convert url alias from name.
     * Null - use value from global store. Or if it is global, then same behavior as on empty string.
     */
    public function getUrlAlias(): ?string
    {
        return $this->getData(OptionSettingInterface::URL_ALIAS);
    }

    public function setUrlAlias(?string $urlAlias): void
    {
        $this->setData(OptionSettingInterface::URL_ALIAS, $urlAlias);
    }

    /**
     * Get attribute option by current option setting
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface|null
     * TODO optimize by change to getLabel. Option needed only for label
     */
    public function getAttributeOption()
    {
        if (!$this->getData('attribute_option')) {
            $value = $this->getOptionId() ?: $this->getValue();
            foreach ($this->getAttributeOptions() as $option) {
                if ($option->getValue() == $value) {
                    $this->setData('attribute_option', $option);
                    break;
                }
            }
        }

        return $this->getData('attribute_option');
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]|null
     * @deprecared DataModel should have only setters and getters
     */
    public function getAttributeOptions()
    {
        return $this->attributeRepository->get($this->getAttributeCode())->getOptions();
    }

    /**
     * @param $content
     * @return string
     * @deprecared DataModel should have only setters and getters
     */
    public function parseWysiwyg($content)
    {
        if ($content) {
            $content = $this->filter->filter((string)$content);
        }

        return $content;
    }
}
