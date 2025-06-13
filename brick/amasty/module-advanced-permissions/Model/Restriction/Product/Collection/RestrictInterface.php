<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Model\Restriction\Product\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

interface RestrictInterface
{
    public function execute(ProductCollection $productCollection): void;
}
