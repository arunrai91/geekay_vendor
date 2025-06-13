<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin;

use Amasty\Rules\Model\ActionsResolver;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

/**
 * Add Amasty Rule actions to select.
 */
class ValueProvider
{
    /**
     * @var \Amasty\Rules\Helper\Data
     */
    private $rulesDataHelper;

    /**
     * @var ActionsResolver
     */
    private $demoActions;

    public function __construct(
        \Amasty\Rules\Helper\Data $rulesDataHelper,
        ActionsResolver $demoActions
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
        $this->demoActions = $demoActions;
    }

    public function afterGetMetadataValues(
        SalesRuleValueProvider $subject,
        $result
    ) {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        foreach ($actions as &$action) {
            if ($action['value'] == \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION) {
                $action['label'] = __("Buy N products, and get next products with discount");
                break;
            }
        }
        $actions = array_merge($actions, $this->rulesDataHelper->getDiscountTypes());
        [$actions, $ActionsForSelect] = $this->demoActions->prepareActions($actions);

        $result['actions']['children']['simple_action']['arguments']['data']['config']['optionsData']
            = $ActionsForSelect;

        return $result;
    }
}
