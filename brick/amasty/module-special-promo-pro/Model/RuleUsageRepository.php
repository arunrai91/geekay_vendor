<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model;

use Amasty\RulesPro\Api\RuleUsageRepositoryInterface;
use Amasty\RulesPro\Model\ResourceModel\RuleUsageCounter;
use Amasty\RulesPro\Model\ResourceModel\RuleUsageLimit;

class RuleUsageRepository implements RuleUsageRepositoryInterface
{

    /**
     * @var array
     */
    private $usageRulesCounts = [];

    /**
     * @var array
     */
    private $usageRulesLimits = [];

    /**
     * @var RuleUsageLimit
     */
    private $ruleUsageLimitResource;

    /**
     * @var RuleUsageCounter
     */
    private $ruleUsageCounterResource;

    public function __construct(
        RuleUsageLimit $ruleUsageLimitResource,
        RuleUsageCounter $ruleUsageCounterResource
    ) {
        $this->ruleUsageCounterResource = $ruleUsageCounterResource;
        $this->ruleUsageLimitResource = $ruleUsageLimitResource;
    }

    /**
     * @param int $salesRuleId
     *
     * @return int
     */
    public function getUsageLimitByRuleId(int $salesRuleId): int
    {
        if (count($this->usageRulesLimits) === 0) {
            $this->updateUsageRulesLimit();
        }

        return $this->usageRulesLimits[$salesRuleId] ?? 0;
    }

    public function updateUsageRulesLimit(): void
    {
        foreach ($this->ruleUsageLimitResource->getLimitByRules() as $limit) {
            $this->usageRulesLimits[$limit['salesrule_id']] = $limit[RuleUsageRepositoryInterface::LIMIT_USAGE_COLUMN];
        }
    }

    public function updateUsageRulesCount(): void
    {
        foreach ($this->ruleUsageCounterResource->getCountByRules() as $count) {
            $this->usageRulesCounts[$count['salesrule_id']] = $count[RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN];
        }
    }

    /**
     * @param int $salesRuleId
     *
     * @return int
     */
    public function getUsageCountByRuleId(int $salesRuleId): int
    {
        if (count($this->usageRulesCounts) === 0) {
            $this->updateUsageRulesCount();
        }

        return $this->usageRulesCounts[$salesRuleId] ?? 0;
    }

    /**
     * @param int $salesRuleId
     * @param int $limit
     */
    public function saveUsageLimit(int $salesRuleId, int $limit)
    {
        $this->ruleUsageLimitResource->saveUsageLimit($salesRuleId, $limit);
    }

    /**
     * @param int $salesRuleId
     * @param int $count
     */
    public function saveUsageCount(int $salesRuleId, int $count)
    {
        $this->ruleUsageCounterResource->saveUsageCount($salesRuleId, $count);
    }

    /**
     * @param array $ruleIds
     */
    public function incrementUsageCountByRuleIds(array $ruleIds)
    {
        $this->ruleUsageCounterResource->incrementUsageCountByRuleIds($ruleIds);
    }

    public function _resetState(): void
    {
        $this->usageRulesLimits = [];
        $this->usageRulesCounts = [];
    }
}
