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

class NotEmpty implements ColumnValidatorInterface
{
    /**
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(PackInterface $pack, string $columnName, ?string $value): void
    {
        if ($value === null) {
            throw new LocalizedException(__(
                'The "%1" value doesn\'t exist. Enter the value and try again.',
                $columnName
            ));
        }
    }
}
