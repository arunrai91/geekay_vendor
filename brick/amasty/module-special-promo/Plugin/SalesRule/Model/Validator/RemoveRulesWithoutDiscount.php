<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Plugin\SalesRule\Model\Validator;

use Amasty\Rules\Model\ConfigModel;
use Amasty\Rules\Model\DiscountRegistry;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Validator;

class RemoveRulesWithoutDiscount
{
    /**
     * @var DiscountRegistry
     */
    private $discountRegistry;

    /**
     * @var ConfigModel
     */
    private $configModel;

    public function __construct(
        DiscountRegistry $discountRegistry,
        ConfigModel $configModel
    ) {
        $this->discountRegistry = $discountRegistry;
        $this->configModel = $configModel;
    }

    public function beforePrepareDescription(Validator $subject, Address $address, string $separator = ', '): array
    {
        if ($this->configModel->showEmptyDiscount()) {
            return [$address, $separator];
        }
        $ruleIdsWithEmptyDiscount = $this->discountRegistry->getRuleIdsWithEmptyDiscount();
        $discountDescriptionArray = $address->getDiscountDescriptionArray();

        foreach ($ruleIdsWithEmptyDiscount as $ruleId) {
            unset($discountDescriptionArray[$ruleId]);
        }

        $address->setDiscountDescriptionArray($discountDescriptionArray);

        return [$address, $separator];
    }
}
