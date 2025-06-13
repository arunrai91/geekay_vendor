<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule\SkipItemsValidator;

use Amasty\Rules\Model\ConfigModel;
use Amasty\Rules\Model\Rule\Action\Discount\AbstractRule;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class ConfigurableProductValidator implements SkipItemValidatorInterface
{
    /**
     * @var ConfigModel
     */
    private $configModel;

    public function __construct(
        ConfigModel $configModel
    ) {
        $this->configModel = $configModel;
    }

    public function validate(AbstractItem $item, Rule $rule): bool
    {
        if (($item->getProduct()->getTypeId() === Configurable::TYPE_CODE)) {
            foreach ($item->getChildren() as $childrenItem) {
                if ($childrenItem->getProduct()->getSpecialPrice()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isNeedToValidate(Rule $rule): bool
    {
        $amrule = $rule->getData(AbstractRule::AMASTY_RULE);
        $useGeneralSkipSettings = $amrule->isEnableGeneralSkipSettings();
        $skipConditions = explode(',', (string)$amrule->getSkipRule());

        return ($useGeneralSkipSettings && $this->configModel->getSkipSpecialPriceConfigurable())
            || (!$useGeneralSkipSettings && in_array(
                SkipItemValidatorInterface::CONFIGURABLE_WITH_SPECIAL_PRICE,
                $skipConditions,
                true
            )
            );
    }
}
