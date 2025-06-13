<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Block\Html;

use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Model\Source\TopmenuLink as TopmenuSource;

class MegaMenu extends Topmenu
{
    /**
     * @param \Amasty\MegaMenuLite\Model\Menu\TreeResolver $subject
     * @param $items
     * @return array
     */
    public function afterGetAdditionalLinks(
        \Amasty\MegaMenuLite\Model\Menu\TreeResolver $subject, // @phpstan-ignore class.notFound
        $items
    ) {
        return $this->getBrandLink($items, TopmenuSource::DISPLAY_LAST);
    }

    /**
     * @param \Amasty\MegaMenuLite\Model\Menu\TreeResolver $subject
     * @param $items
     * @return array
     */
    public function afterGetBeforeAdditionalLinks(
        \Amasty\MegaMenuLite\Model\Menu\TreeResolver $subject, // @phpstan-ignore class.notFound
        $items
    ) {
        return $this->getBrandLink($items, TopmenuSource::DISPLAY_FIRST);
    }

    /**
     * @param $items
     * @param $displaySetting
     * @return array
     */
    public function getBrandLink($items, $displaySetting)
    {
        $position = $this->scopeConfig->getValue(
            'amshopby_brand/general/topmenu_enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($this->isEnabled() && $position == $displaySetting) {
            $data = $this->_getNodeAsArray();
            $data['content'] = $this->brandsPopup->isShowPopup() ? $this->brandsPopup->getOnlyContent() : null;
            $data['width'] = 1;
            $items[] = $data;
        }

        return $items;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        $topMenuEnabled = $this->scopeConfig->getValue(
            'amshopby_brand/general/topmenu_enabled',
            ScopeInterface::SCOPE_STORE
        );
        $brandExist = $this->scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            ScopeInterface::SCOPE_STORE
        );

        return $topMenuEnabled && $brandExist;
    }
}
