<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Validation\Group;

class GroupValidator
{
    /**
     * @var ValidatorPool
     */
    private ValidatorPool $validatorPool;

    public function __construct(
        ValidatorPool $validatorPool
    ) {
        $this->validatorPool = $validatorPool;
    }

    public function validateGroup($group): bool
    {
        foreach ($this->validatorPool->getValidators() as $validator) {
            if (!$validator->validate($group)) {
                return false;
            }
        }

        return true;
    }
}
