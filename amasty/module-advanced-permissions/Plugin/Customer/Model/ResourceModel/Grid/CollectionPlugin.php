<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Customer\Model\ResourceModel\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection;

class CollectionPlugin
{
    public function beforeAddFieldToFilter(Collection $subject, $field, $condition): ?array
    {
        if (is_string($field)
            && (strpos($field, '.') === false)
            && $subject->getConnection()->tableColumnExists($subject->getMainTable(), $field)
        ) {
            return ['main_table.' . $field, $condition];
        }

        return null;
    }
}
