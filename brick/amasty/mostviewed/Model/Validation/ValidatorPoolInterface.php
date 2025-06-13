<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Validation;

interface ValidatorPoolInterface
{
    /**
     * @return \Amasty\Mostviewed\Model\Validation\ValidatorInterface[]
     */
    public function getValidators(): array;
}
