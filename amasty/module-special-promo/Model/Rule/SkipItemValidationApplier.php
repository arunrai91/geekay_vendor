<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\Rule;

use Amasty\Rules\Model\Rule\SkipItemsValidator\SkipItemValidatorInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class SkipItemValidationApplier
{
    /**
     * @var SkipItemValidatorInterface[]
     */
    private $skipItemValidatorPool;

    public function __construct(
        array $skipItemValidatorPool = []
    ) {
        $this->initializeItemValidators($skipItemValidatorPool);
    }

    public function isNeedToSkipItem(AbstractItem $item, Rule $rule): bool
    {
        foreach ($this->skipItemValidatorPool as $validator) {
            if ($validator->isNeedToValidate($rule) && $validator->validate($item, $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SkipItemValidatorInterface[] $skipItemValidators
     */
    public function initializeItemValidators(array $skipItemValidators): void
    {
        foreach ($skipItemValidators as $name => $validator) {
            if (!($validator instanceof SkipItemValidatorInterface)) {
                throw new \LogicException(
                    'Type "' . get_class($validator) . '" is not instance of ' . SkipItemValidatorInterface::class
                );
            }
            $this->skipItemValidatorPool[$name] = $validator;
        }
    }
}
