<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Promotions Manager for Magento 2
 */

namespace Amasty\Rgrid\Model;

use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;

class DuplicateRuleProcessor
{
    public const COUNT_USAGE_COLUMN = 'count';

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param int $ruleId
     * @return RuleInterface
     */
    public function execute(int $ruleId): RuleInterface
    {
        /** @var RuleInterface $rule */
        $rule = $this->ruleRepository->getById($ruleId);
        $rule->setRuleId(null);
        $attributes = $rule->getExtensionAttributes() ? : [];
        if (is_array($attributes)) {
            $attributes[self::COUNT_USAGE_COLUMN] = 0;
        } elseif (method_exists($attributes, 'getCount')) {
            $attributes->setCount(0);
        }
        $rule->setExtensionAttributes($attributes);

        $rule = $this->ruleRepository->save($rule);

        return $rule;
    }
}
