<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales;

use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\GetAggregatedByPackTable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class GetAggregatedTable
{
    public const VIEW_NAME = 'amasty_mostviewed_pack_sales_aggregated';
    public const PACK_COLUMN = 'pack_id';
    public const COUNT_COLUMN = 'orders_count';

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var GetAggregatedByPackTable
     */
    private GetAggregatedByPackTable $getAggregatedByPackTable;

    public function __construct(
        GetAggregatedByPackTable $getAggregatedByPackTable,
        ResourceConnection $resourceConnection,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configProvider = $configProvider;
        $this->getAggregatedByPackTable = $getAggregatedByPackTable;
    }

    public function execute(): Select
    {
        $orderTable = $this->resourceConnection->getTableName('sales_order');
        $packAnalyticOrderStatuses = $this->configProvider->getPackAnalyticOrderStatuses();
        $condition = sprintf('pack_sales.%s = sales_order.entity_id', PackHistoryTable::ORDER_COLUMN);

        if (!empty($packAnalyticOrderStatuses)) {
            $condition .= sprintf(
                ' AND sales_order.status IN (%s)',
                $this->resourceConnection->getConnection()->quoteInto('?', $packAnalyticOrderStatuses)
            );
        }

        return $this->getAggregatedByPackTable->execute()
            ->join(['sales_order' => $orderTable], $condition)
            ->group('pack_id');
    }
}
