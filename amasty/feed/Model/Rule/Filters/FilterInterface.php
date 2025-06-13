<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Rule\Filters;

use Amasty\Feed\Model\Feed;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

interface FilterInterface
{
    public function apply(Collection $productCollection, Feed $model): void;
}
