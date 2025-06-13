<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Ui\Form\Rule;

use Amasty\Rules\Model\Rule\ItemCalculationPrice;

/**
 * Options for dropdown "Calculate Discount Based On".
 * For usage @see ItemCalculationPrice
 */
class CalculateDiscountBasedOnOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => ItemCalculationPrice::DEFAULT_PRICE, 'label' => __('Price (Special Price if Set)')],
            ['value' => ItemCalculationPrice::DISCOUNTED_PRICE, 'label' => __('Price after Previous Discount(s)')],
            [
                'value' => ItemCalculationPrice::ORIGIN_PRICE,
                'label' => __('Original Price, Calculate before Special Price (if present)')
            ],
            [
                'value' => ItemCalculationPrice::ORIGIN_WITH_REVERT,
                'label' => __('Original Price, Apply to Original Price (skip if result is more than Special Price)')
            ],
        ];
    }
}
