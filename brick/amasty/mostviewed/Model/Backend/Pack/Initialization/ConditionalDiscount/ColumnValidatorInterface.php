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

interface ColumnValidatorInterface
{
    /**
     * @param PackInterface $pack
     * @param string $columnName
     * @param string|null $value
     * @return void
     * @throws LocalizedException
     */
    public function validate(PackInterface $pack, string $columnName, ?string $value): void;
}
