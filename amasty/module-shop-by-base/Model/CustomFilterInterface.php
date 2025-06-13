<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model;

/**
 * @api SPI
 */
interface CustomFilterInterface
{
    /**
     * Get Filter Settings code.
     *
     * Usually it is same as related attribute code.
     *
     * @return string
     */
    public function getFilterCode(): string;
}
