<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\OptionSource;

use Amasty\Acart\Model\Rule as Rule;
use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            RULE::RULE_ACTIVE => __('Active'),
            RULE::RULE_INACTIVE => __('Inactive'),
        ];
    }
}
