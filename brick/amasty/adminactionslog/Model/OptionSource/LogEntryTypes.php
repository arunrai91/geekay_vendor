<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\OptionSource;

class LogEntryTypes implements \Magento\Framework\Data\OptionSourceInterface
{
    public const TYPE_NEW = 'new';
    public const TYPE_EDIT = 'edit';
    public const TYPE_DELETE = 'delete';
    public const TYPE_CACHE = 'cache';
    public const TYPE_EXPORT = 'export';
    public const TYPE_RESTORE = 'restore';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::TYPE_NEW => __('New'),
            self::TYPE_EDIT => __('Edit'),
            self::TYPE_DELETE => __('Delete'),
            self::TYPE_CACHE => __('Cache'),
            self::TYPE_EXPORT => __('Export'),
            self::TYPE_RESTORE => __('Restore'),
        ];
    }
}
