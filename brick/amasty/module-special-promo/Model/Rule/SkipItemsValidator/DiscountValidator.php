<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\Rule\Action\Discount\AbstractRule;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class DiscountValidator implements SkipItemValidatorInterface
{
    public function validate(AbstractItem $item, Rule $rule): bool
    {
        $appliedRuleIds = array_map(
            'trim',
            explode(',', (string)$item->getData('applied_rule_ids'))
        );

        return $item->getData('discount_amount') && !in_array($rule->getId(), $appliedRuleIds);
    }

    public function isNeedToValidate(Rule $rule): bool
    {
        $amrule = $rule->getData(AbstractRule::AMASTY_RULE);
        $skipConditions = explode(',', (string)$amrule->getSkipRule());
        $useGeneralSkipSettings = $amrule->isEnableGeneralSkipSettings();

        return (!$useGeneralSkipSettings
            && in_array(SkipItemValidatorInterface::DISCOUNT_PRICE, $skipConditions, true));
    }
}
