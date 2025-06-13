<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Validation;

use Magento\Framework\Model\AbstractModel;

interface ValidatorInterface
{
    /**
     * @param AbstractModel $entity
     * @return bool
     */
    public function validate(AbstractModel $entity): bool;
}
