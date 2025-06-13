<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Magento\Framework\Exception\LocalizedException;

class GreaterThanOne implements ColumnValidatorInterface
{
    public function validate(PackInterface $pack, string $columnName, ?string $value): void
    {
        if (!$pack->getApplyForParent()) {
            return;
        }

        $value = (int) $value;
        if ($value < 2) {
            throw new LocalizedException(__('Please set "%1" higher than 1', $columnName));
        }
    }
}
