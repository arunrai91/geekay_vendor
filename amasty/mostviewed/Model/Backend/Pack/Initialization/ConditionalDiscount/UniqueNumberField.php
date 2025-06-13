<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Magento\Framework\Exception\LocalizedException;

class UniqueNumberField implements ValidatorInterface
{
    /**
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(array $discountsData): void
    {
        $numberItems = array_column($discountsData, ConditionalDiscountInterface::NUMBER_ITEMS);
        $uniqueNumberItems = array_unique($numberItems);

        if (count($numberItems) > count($uniqueNumberItems)) {
            throw new LocalizedException(__('Please set different amounts of Individual Bundle Items'));
        }
    }
}
