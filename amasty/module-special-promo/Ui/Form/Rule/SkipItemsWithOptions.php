<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Ui\Form\Rule;

use Magento\Framework\Data\OptionSourceInterface;

class SkipItemsWithOptions implements OptionSourceInterface
{
    public const SPECIAL_PRICE = 1;
    public const TIER_PRICE = 2;
    public const DISCOUNT_PRICE = 3;
    public const CONFIGURABLE_WITH_SPECIAL_PRICE = 4;

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::SPECIAL_PRICE, 'label' => __('Special Price (incl. Catalog Price Rules)')],
            ['value' => self::TIER_PRICE, 'label' => __('Tier Price')],
            ['value' => self::DISCOUNT_PRICE, 'label' => __('Cart Price Rules Discount')],
            [
                'value' => self::CONFIGURABLE_WITH_SPECIAL_PRICE,
                'label' => __('Configurable when Child has Special Price')
            ],
        ];
    }
}
