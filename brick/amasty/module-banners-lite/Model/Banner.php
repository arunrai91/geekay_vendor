<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model;

use Amasty\BannersLite\Api\Data\BannerInterface;
use Magento\Framework\Model\AbstractModel;

class Banner extends AbstractModel implements BannerInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Banner::class);
        $this->setIdFieldName(BannerInterface::ENTITY_ID);
    }

    public function getSalesruleId()
    {
        return $this->_getData(BannerInterface::SALESRULE_ID);
    }

    public function setSalesruleId($salesruleId)
    {
        $this->setData(BannerInterface::SALESRULE_ID, $salesruleId);

        return $this;
    }

    public function getBannerImage()
    {
        return $this->_getData(BannerInterface::BANNER_IMAGE);
    }

    public function setBannerImage($bannerImage)
    {
        $this->setData(BannerInterface::BANNER_IMAGE, $bannerImage);

        return $this;
    }

    public function getBannerAlt()
    {
        return $this->_getData(BannerInterface::BANNER_ALT);
    }

    public function setBannerAlt($bannerAlt)
    {
        $this->setData(BannerInterface::BANNER_ALT, $bannerAlt);

        return $this;
    }

    public function getBannerHoverText()
    {
        return $this->_getData(BannerInterface::BANNER_HOVER_TEXT);
    }

    public function setBannerHoverText($bannerHoverText)
    {
        $this->setData(BannerInterface::BANNER_HOVER_TEXT, $bannerHoverText);

        return $this;
    }

    public function getBannerLink()
    {
        return $this->_getData(BannerInterface::BANNER_LINK);
    }

    public function setBannerLink($bannerLink)
    {
        $this->setData(BannerInterface::BANNER_LINK, $bannerLink);

        return $this;
    }

    public function getBannerType()
    {
        return $this->_getData(BannerInterface::BANNER_TYPE);
    }

    public function setBannerType($bannerType)
    {
        $this->setData(BannerInterface::BANNER_TYPE, $bannerType);

        return $this;
    }
}
