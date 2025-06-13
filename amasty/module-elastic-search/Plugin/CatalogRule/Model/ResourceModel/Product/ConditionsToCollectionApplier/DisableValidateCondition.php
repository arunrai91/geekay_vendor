<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Plugin\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;

use Amasty\ElasticSearch\Model\Rule\Condition\CustomConditionInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor;

/**
 * Remove our custom bestseller condition because Magento when forming product collection validates
 * custom condition types and throws an error if they do not match.
 * @see MappableConditionsProcessor::rebuildCombinedCondition
 */
class DisableValidateCondition
{
    /**
     * @see ConditionsToCollectionApplier::applyConditionsToCollection()
     *
     * @param ConditionsToCollectionApplier $subject
     * @param Combine $conditions
     * @param ProductCollection $productCollection
     * @return array
     */
    public function beforeApplyConditionsToCollection(
        ConditionsToCollectionApplier $subject,
        Combine $conditions,
        ProductCollection $productCollection
    ): array {
        $newConditions = clone $conditions;
        $this->removeOurConditions($newConditions);

        return [$newConditions, $productCollection];
    }

    private function removeOurConditions(Combine $conditions): void
    {
        $conditionsArray = [];

        foreach ($conditions->getConditions() as $condition) {
            if (!$condition instanceof CustomConditionInterface) {
                if ($condition instanceof Combine) {
                    $newCondition = clone $condition;
                    $this->removeOurConditions($newCondition);
                    $condition = $newCondition;
                }

                $conditionsArray[] = $condition;
            }
        }

        $conditions->setConditions($conditionsArray);
    }
}
