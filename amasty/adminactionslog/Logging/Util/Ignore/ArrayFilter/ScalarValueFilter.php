<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Util\Ignore\ArrayFilter;

class ScalarValueFilter
{
    public function filter(array $data): array
    {
        return array_filter($data, function ($value) {
            return is_scalar($value) && !is_array($value);
        });
    }
}
