<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model;

class ActionsResolver
{
    /**
     * @var array
     */
    private $demoEntities = [];

    public function __construct(
        array $demoEntities = []
    ) {
        $this->prepareDemoEntities($demoEntities);
    }

    public function prepareActions(array $actions): array
    {
        $preparedActions = [];
        $ActionsForSelect = [];
        foreach ($actions as $action) {
            $optgroup = [];

            $actionValue = is_array($action['value']) ? $action['label']->render() : $action['value'];
            $optgroupItems = is_array($action['value']) ? $action['value'] : [];

            foreach ($optgroupItems as $type) {
                $optgroup[] = [
                    'value' => $type['value'],
                    'label' => is_string($type['label']) ? $type['label'] : $type['label']->render(),
                    'isPromo' => $type['isPromo'] ?? false,
                    'promoLink' => $type['promoLink'] ?? false,
                ];
            }

            $label = $action['label']->render();
            if (isset($this->demoEntities[$label])) {
                foreach ($this->demoEntities[$label] as $demoEntity) {
                    $optgroup[] = $demoEntity->toArray();
                }
            }

            $preparedAction = [
                'value' => $actionValue,
                'label' => $label,
                'isPromo' => $action['isPromo'] ?? false,
            ];

            if (!empty($optgroup)) {
                $preparedAction['optgroup'] = $optgroup;
                $preparedAction['labelsDecoration'] = true;
                foreach ($optgroup as $optgroupItem) {
                    $ActionsForSelect[] = ['value' => $optgroupItem['value'], 'label' => $optgroupItem['label']];
                }
            } else {
                $ActionsForSelect[] = ['value' => $actionValue, 'label' => $label];
            }

            $preparedActions[] = $preparedAction;
        }

        return [$preparedActions, $ActionsForSelect];
    }

    private function prepareDemoEntities(array $demoEntities): void
    {
        $result = [];
        foreach ($demoEntities as $entity) {
            if (is_array($entity) && isset($entity['groupName']) && isset($entity['actions'])) {
                if (!isset($result[$entity['groupName']])) {
                    $result[$entity['groupName']] = [];
                }
                $result[$entity['groupName']] += $entity['actions'];
            }
        }
        $this->demoEntities = $result;
    }
}
