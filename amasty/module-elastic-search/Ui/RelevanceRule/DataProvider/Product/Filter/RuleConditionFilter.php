<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Ui\RelevanceRule\DataProvider\Product\Filter;

use Amasty\ElasticSearch\Model\RuleFactory as RelevanceRuleConditionsFactory;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Laminas\Uri\Uri as LaminasUri;

class RuleConditionFilter implements AddFilterToCollectionInterface
{
    public const MATCHED_FLAG = 'matched_products';
    public const DEFAULT_WEBSITE = 0;

    /**
     * @var RelevanceRuleConditionsFactory
     */
    private $relevanceRuleFactory;

    /**
     * @var LaminasUri
     */
    private $laminasUri;

    public function __construct(
        RelevanceRuleConditionsFactory $relevanceRuleFactory,
        LaminasUri $laminasUri
    ) {
        $this->relevanceRuleFactory = $relevanceRuleFactory;
        $this->laminasUri = $laminasUri;
    }

    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $conditions = $this->conditionArray($condition['eq'] ?? '');

        if (empty($condition)) {
            $collection->addIdFilter(null);
        } else {
            $matchedIds = $this->getMatchedProductsIds($conditions);

            if (!empty($matchedIds)) {
                $collection->addIdFilter($matchedIds);
            } else {
                $collection->getSelect()->where('null');
            }
        }
    }

    /**
     * @param array[] $conditions
     *
     * @return int[]
     */
    public function getMatchedProductsIds(array $conditions): array
    {
        $matchedProductIds = [];
        $rule = $this->relevanceRuleFactory->create();
        $rule->loadPost($conditions['rule'] ?? []);
        $website = $conditions['websites'] ?? [self::DEFAULT_WEBSITE];
        $rule->setWebsiteIds(join(',', $website));

        if (!$this->isRuleEmpty($rule)) {
            $matchedProducts = $rule->getMatchingProductIds();
            $matchedProductIds = array_map('intval', array_unique(array_keys($matchedProducts)));
        }

        return $matchedProductIds;
    }

    private function isRuleEmpty(\Magento\CatalogRule\Model\Rule $rule): bool
    {
        $conditionsArray = $rule->getConditions()->asArray();

        return empty($conditionsArray['conditions']);
    }

    private function conditionArray(string $serializedConditions): array
    {
        $this->laminasUri->setQuery($serializedConditions);

        return $this->laminasUri->getQueryAsArray();
    }
}
