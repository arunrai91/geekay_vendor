<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Rule\Validator;

use Amasty\Rules\Api\ExtendedValidatorInterface;
use Magento\Quote\Model\Quote\Item;
use Amasty\RulesPro\Api\RuleUsageRepositoryInterface;
use Amasty\Rules\Model\RuleResolver;

class UsageLimitValidator implements ExtendedValidatorInterface
{
    /**
     * @var RuleUsageRepositoryInterface
     */
    private $ruleUsageRepository;

    /**
     * @var RuleResolver
     */
    private $ruleResolver;

    public function __construct(
        RuleUsageRepositoryInterface $ruleUsageRepository,
        RuleResolver $ruleResolver
    ) {
        $this->ruleUsageRepository = $ruleUsageRepository;
        $this->ruleResolver = $ruleResolver;
    }

    /**
     * @param $combineCondition
     * @param $type
     *
     * @return bool|null
     */
    public function validate($combineCondition, $type)
    {
        if ($type instanceof Item) {
            $validate = $this->isValidUsageLimit($combineCondition);
            if (!$validate) {
                return false;
            }
        }

        return null;
    }

    /**
     * @param $combineCondition
     *
     * @return bool
     */
    protected function isValidUsageLimit($combineCondition)
    {
        $rule = $combineCondition->getRule();
        $ruleId = $this->ruleResolver->getLinkId($rule);
        $count = $this->ruleUsageRepository->getUsageCountByRuleId((int)$rule->getRuleId());
        $limit = $this->ruleUsageRepository->getUsageLimitByRuleId((int)$ruleId);

        if ($limit != 0 && $count >= $limit) {
            return false;
        }

        return true;
    }
}
