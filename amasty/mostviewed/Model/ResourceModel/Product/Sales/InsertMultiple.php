<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Product\Sales;

use Amasty\Mostviewed\Model\ResourceModel\Product\Analytic\Table;
use Magento\Framework\App\ResourceConnection;

class InsertMultiple
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(array $data): void
    {
        $this->resourceConnection->getConnection('sales')->insertMultiple(
            $this->resourceConnection->getTableName(Table::TABLE_NAME),
            $data
        );
    }
}
