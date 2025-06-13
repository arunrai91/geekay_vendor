<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Price;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;

class RemoveExtraZeros
{
    /**
     * Results in an int if the float value is equivalent to the value cast to an int
     *
     * @return int|float
     */
    public function execute(FilterSettingInterface $settings, float $price)
    {
        return $settings->getHideZeros() && (int)$price == $price
            ? (int)$price
            : $price;
    }
}
