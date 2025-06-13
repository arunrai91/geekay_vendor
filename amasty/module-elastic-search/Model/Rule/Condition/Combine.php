<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Combine as MagentoCombineRule;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;

class Combine extends MagentoCombineRule
{
    private const SORTING_CONDITIONS = 'amasty_sorting';

    public function getNewChildSelectOptions(): array
    {
        $conditions = AbstractCondition::getNewChildSelectOptions();
        $conditions[] = ['label' => __('Conditions Combination'), 'value' => static::class];
        $conditions[] = ['label' => __('Product Attribute'), 'value' => $this->getProductConditions()];
        $conditions[] = [
            'label' => $this->getSortingConditionsLabel(),
            'value' => $this->isSortingEnabled() ? $this->getSortingConditions() : []
        ];

        return $conditions;
    }

    private function getSortingConditions(): array
    {
        $sortingConditions = (array) $this->getData(self::SORTING_CONDITIONS);

        return array_reduce($sortingConditions, [$this, 'formatCondition'], []);
    }

    /**
     * Formation of an array of conditions in the required format
     *
     * @param array $carry
     * @param CustomConditionInterface $condition
     * @return array
     */
    private function formatCondition(array $carry, CustomConditionInterface $condition): array
    {
        $carry[] = [
            'label' => $condition->getAttributeElementHtml(),
            'value' => ltrim(get_class($condition), '/')
        ];

        return $carry;
    }

    private function getSortingConditionsLabel(): Phrase
    {
        $title = __('Improved Sorting (not installed)');

        if ($this->isSortingEnabled()) {
            $title = __('Improved Sorting');
        }

        return $title;
    }

    private function isSortingEnabled(): bool
    {
        return $this->getData('module_manager') && $this->getData('module_manager')->isEnabled('Amasty_Sorting');
    }

    private function getProductConditions(): array
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];

        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function getOperatorElementHtml(): string
    {
        return $this->getOperatorElement()->getHtml();
    }
}
