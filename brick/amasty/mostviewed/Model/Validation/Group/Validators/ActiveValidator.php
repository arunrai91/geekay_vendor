<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Validation\Group\Validators;

use Amasty\Mostviewed\Model\Validation\ValidatorInterface;
use Magento\Framework\Model\AbstractModel;

class ActiveValidator implements ValidatorInterface
{
    public function validate(AbstractModel $entity): bool
    {
        if (!$entity->getGroupId()) {
            return false;
        }

        return (bool)$entity->getStatus();
    }
}
