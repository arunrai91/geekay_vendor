<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\SalesRule;

use Amasty\Rules\Model\RuleResolver;
use Amasty\RulesPro\Api\RuleUsageRepositoryInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
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
     * @param object $entity
     * @param array $arguments
     *
     * @return bool|object|void
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $rule = $entity;
        $ruleExtensionAttributes = $rule->getExtensionAttributes();
        $ruleId = $this->ruleResolver->getLinkId($rule);

        if (empty($ruleExtensionAttributes[RuleUsageRepositoryInterface::LIMIT_USAGE_COLUMN])) {
            $ruleExtensionAttributes[RuleUsageRepositoryInterface::LIMIT_USAGE_COLUMN] = 0;
        }

        $this->ruleUsageRepository->saveUsageLimit(
            (int)$ruleId,
            (int)$ruleExtensionAttributes[RuleUsageRepositoryInterface::LIMIT_USAGE_COLUMN]
        );

        if (empty($ruleExtensionAttributes[RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN])) {
            $ruleExtensionAttributes[RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN] = 0;
        }

        $this->ruleUsageRepository->saveUsageCount(
            (int)$rule->getRuleId(),
            (int)$ruleExtensionAttributes[RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN]
        );

        return $entity;
    }
}
