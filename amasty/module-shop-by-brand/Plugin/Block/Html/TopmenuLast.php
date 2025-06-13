<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Plugin\Block\Html;

use Amasty\ShopbyBrand\Model\Source\TopmenuLink as TopmenuSource;

class TopmenuLast extends \Amasty\ShopbyBrand\Plugin\Block\Html\Topmenu
{
    /**
     * @return int
     */
    protected function getPosition()
    {
        return TopmenuSource::DISPLAY_LAST;
    }
}
