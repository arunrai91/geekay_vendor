<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api;

/**
 * @api
 */
interface RelevanceRuleRepositoryInterface
{
    /**
     * @param Data\RelevanceRuleInterface $rule
     * @return Data\SynonymInterface
     */
    public function save(Data\RelevanceRuleInterface $rule);

    /**
     * @param int $ruleId|null
     * @return Data\RelevanceRuleInterface
     */
    public function get($ruleId);

    /**
     * @param Data\RelevanceRuleInterface $rule
     * @return bool true on success
     */
    public function delete(Data\RelevanceRuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool true on success
     */
    public function deleteById($ruleId);

    /**
     * @return \Amasty\ElasticSearch\Model\ResourceModel\RelevanceRule\Collection
     */
    public function getActiveRules();

    /**
     * @param int[]|null $productIds
     * @param int|null $websiteId
     * @return array
     */
    public function getProductBoostMultipliers(?array $productIds = null, ?int $websiteId = null): array;
}
