<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\ResourceModel\Search\FilterMapper\CustomExclusionStrategy;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;

interface OperationInterface
{
    public function applyFilter(FilterInterface $filter, Select $select): void;
}
