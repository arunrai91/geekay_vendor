<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\SalesRule;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;
use Amasty\RulesPro\Api\RuleUsageRepositoryInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var RuleUsageRepositoryInterface
     */
    private $ruleUsageRepository;

    public function __construct(
        MetadataPool $metadataPool,
        RuleUsageRepositoryInterface $ruleUsageRepository
    ) {
        $this->metadataPool = $metadataPool;
        $this->ruleUsageRepository = $ruleUsageRepository;
    }

    /**
     * Fill Sales Rule extension attributes with related Special Promotions Rule
     *
     * @param object $entity
     * @param array $arguments
     *
     * @return bool|object
     */
    public function execute($entity, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = $entity->getDataByKey($linkField);

        if ($ruleLinkId) {
            /** @var array $attributes */
            $attributes = $entity->getExtensionAttributes() ? : [];
            $attributes[RuleUsageRepositoryInterface::LIMIT_USAGE_COLUMN] =
                $this->ruleUsageRepository->getUsageLimitByRuleId($ruleLinkId) ? : 0;
            $attributes[RuleUsageRepositoryInterface::COUNT_USAGE_COLUMN] =
                $this->ruleUsageRepository->getUsageCountByRuleId($entity->getRuleId()) ? : 0;
            $entity->setExtensionAttributes($attributes);
        }

        return $entity;
    }
}
