<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin\SalesRule\Model\Utility;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility;

class SkipDiscountQtyValidation
{
    /**
     * @var string[]
     */
    private $actionsToSkip = [];

    public function __construct($actionsToSkip)
    {
        $this->actionsToSkip = array_merge($this->actionsToSkip, $actionsToSkip);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemQty(Utility $subject, int $result, AbstractItem $item, Rule $rule): int
    {
        // skip magento validation for setting discount_qty
        // because magento use this setting to validate ItemsQty,
        // but we need validate RuleAppliedQty
        if (in_array($rule->getSimpleAction(), $this->actionsToSkip)) {
            return (int)$item->getTotalQty();
        }

        return $result;
    }
}
