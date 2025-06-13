<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation\Widget;

interface WidgetInterface
{
    /**
     * @param \Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting
     *
     * @return mixed
     */
    public function setFilterSetting(\Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting);

    /**
     * @return mixed
     */
    public function getFilterSetting();
}
