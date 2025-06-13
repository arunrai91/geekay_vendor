<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class LoginAttemptStatus implements OptionSourceInterface
{
    public const FAILED = 0;
    public const SUCCESS = 1;
    public const LOGOUT = 2;

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['label' => $label, 'value' => $value];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::FAILED => __('Failed'),
            self::SUCCESS => __('Success'),
            self::LOGOUT => __('Logout')
        ];
    }
}
