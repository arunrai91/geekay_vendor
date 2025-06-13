<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model;

use Amasty\Rules\Helper\Data;

class DiscountTypes
{
    public const AMASTY_RULES_ACTIONS = [
        Data::TYPE_XY_ANY_PRODUCTS,
        Data::TYPE_CHEAPEST,
        Data::TYPE_EXPENCIVE,
        Data::TYPE_AMOUNT,
        Data::TYPE_EACH_N,
        Data::TYPE_EACH_N_FIXDISC,
        Data::TYPE_EACH_N_FIXED,
        Data::TYPE_EACH_M_AFT_N_PERC,
        Data::TYPE_EACH_M_AFT_N_DISC,
        Data::TYPE_EACH_M_AFT_N_FIX,
        Data::TYPE_GROUP_N,
        Data::TYPE_GROUP_N_DISC,
        Data::TYPE_XY_PERCENT,
        Data::TYPE_XY_FIXED,
        Data::TYPE_XY_FIXDISC,
        Data::TYPE_XN_PERCENT,
        Data::TYPE_XN_FIXED,
        Data::TYPE_XN_FIXDISC,
        Data::TYPE_TIERED_WHOLE_CART,
        Data::TYPE_TIERED_XN,
        Data::TYPE_AFTER_N_FIXED,
        Data::TYPE_AFTER_N_DISC,
        Data::TYPE_AFTER_N_FIXDISC,
        Data::TYPE_SETOF_PERCENT,
        Data::TYPE_SETOF_FIXED
    ];
}
