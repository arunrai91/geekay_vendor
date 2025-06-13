<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Slider;

use Magento\Catalog\Model\Product;

interface SliderProductsProviderInterface
{
    /**
     * @return Product[]
     */
    public function getProducts(): iterable;
}
