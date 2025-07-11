<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Block\Html;

use Amasty\ShopbyBrand\Block\BrandsPopup as BrandsPopupBlock;
use Amasty\ShopbyBrand\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Model\Source\TopmenuLink as TopmenuSource;

class Topmenu
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @var BrandsPopupBlock
     */
    private BrandsPopupBlock $brandsPopup;

    /**
     * @var string|null
     */
    private ?string $label = null;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        Helper $helper,
        BrandsPopupBlock $brandsPopup
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->url = $url;
        $this->brandsPopup = $brandsPopup;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $html
     * @return string
     */
    public function afterGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $html
    ) {
        if (!$this->isEnabled()) {
            return $html;
        }

        $brandPopup = $this->brandsPopup->toHtml();
        if ($brandPopup) {
            $html = $this->getPosition() == TopmenuSource::DISPLAY_FIRST ?
                $brandPopup . $html :
                $html . $brandPopup;
        }

        return $html;
    }

    /**
     * @return array
     */
    public function _getNodeAsArray()
    {
        $url = $this->helper->getAllBrandsUrl();
        return [
            'name' => $this->getLabel(),
            'id' => 'amasty_shopby_brand_allbrands',
            'url' => $url,
            'has_active' => false,
            'is_active' => $url == $this->url->getCurrentUrl()
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $topMenuEnabled = $this->scopeConfig->getValue(
            'amshopby_brand/general/topmenu_enabled',
            ScopeInterface::SCOPE_STORE
        );

        $brandExist = $this->scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            ScopeInterface::SCOPE_STORE
        );

        return $this->getPosition() == $topMenuEnabled && $brandExist;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return TopmenuSource::DISPLAY_FIRST;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if ($this->label) {
            $label = $this->label;
        } else {
            $label = $this->scopeConfig->getValue(
                'amshopby_brand/general/menu_item_label',
                ScopeInterface::SCOPE_STORE
            );
        }

        return $label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    public function getBrandsPopupBlock(): BrandsPopupBlock
    {
        return $this->brandsPopup;
    }
}
