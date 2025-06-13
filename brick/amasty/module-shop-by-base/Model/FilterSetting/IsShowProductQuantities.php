<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\ShopbyBase\Model\Source\ShowProductQuantities;
use Magento\Catalog\Helper\Data;

class IsShowProductQuantities
{
    /**
     * @var Data
     */
    private $catalogHelper;

    public function __construct(
        Data $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    public function execute(?int $showProductQuantities): bool
    {
        return $showProductQuantities == ShowProductQuantities::SHOW_DEFAULT || $showProductQuantities === null
            ? $this->catalogHelper->shouldDisplayProductCountOnLayer()
            : $showProductQuantities != ShowProductQuantities::SHOW_NO;
    }
}
