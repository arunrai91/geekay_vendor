<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search\DataProvider\Aggregation\CustomFilterPool;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;

interface OperationInterface
{
    public const ACTIVE_PATH = '/enabled';

    public function isActive(): bool;

    public function getAggregation(Table $entityIdsTable, array $dimensions = []): Select;
}
