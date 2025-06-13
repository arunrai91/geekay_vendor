<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\SkipItemsValidator;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

interface SkipItemValidatorInterface
{
    public const SPECIAL_PRICE = '1';
    public const TIER_PRICE = '2';
    public const DISCOUNT_PRICE = '3';
    public const CONFIGURABLE_WITH_SPECIAL_PRICE = '4';

    public function validate(AbstractItem $item, Rule $rule): bool;

    public function isNeedToValidate(Rule $rule): bool;
}
