<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Ui\Component\Listing\Columns;

class SliderImage extends Image
{
    /**
     * @param \Amasty\ShopbyBase\Api\Data\OptionSettingInterface $brand
     * @return null|string
     */
    protected function getImage(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface $brand)
    {
        return $brand->getSliderImageUrl()
            ? $brand->getSliderImageUrl()
            : $this->imageHelper->getDefaultPlaceholderUrl();
    }
}
