<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Util;

class ClassNameNormalizer
{
    public function execute(string $className): string
    {
        return str_replace(['\\Interceptor', '\\Proxy'], '', $className);
    }
}
