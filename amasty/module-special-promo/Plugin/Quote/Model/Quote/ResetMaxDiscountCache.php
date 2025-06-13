<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin\Quote\Model\Quote;

use Amasty\Rules\Helper\Discount;
use Magento\Quote\Model\Quote;

class ResetMaxDiscountCache
{
    /**
     * @param Quote $subject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCollectTotals(Quote $subject): array
    {
        Discount::$maxDiscount = [];
        
        return [];
    }
}
