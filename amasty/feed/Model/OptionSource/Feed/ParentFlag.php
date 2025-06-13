<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\OptionSource\Feed;

use Magento\Framework\Data\OptionSourceInterface;

class ParentFlag implements OptionSourceInterface
{
    public const NO = 'no';
    public const YES = 'yes';
    public const YES_IF_EMPTY = 'if_empty';
    public const YES_STRICT = 'strict';

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
            self::NO => __('No'),
            self::YES => __('Yes'),
            self::YES_IF_EMPTY => __('Yes if empty'),
            self::YES_STRICT => __('Yes (strict)')
        ];
    }
}
