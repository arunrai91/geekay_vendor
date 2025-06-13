<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Search\DataProvider\Aggregation\CustomFilterPool;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Inventory\Resolver as InventoryResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockResource;

class StockStatus implements OperationInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockResource
     */
    private $stockResource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    public function __construct(
        ConfigProvider $configProvider,
        ResourceConnection $resource,
        StockResource $stockResource,
        ScopeResolverInterface $scopeResolver,
        InventoryResolver $inventoryResolver
    ) {
        $this->configProvider = $configProvider;
        $this->resource = $resource;
        $this->stockResource = $stockResource;
        $this->scopeResolver = $scopeResolver;
        $this->inventoryResolver = $inventoryResolver;
    }

    public function isActive(): bool
    {
        return $this->configProvider->isStockFilterEnabled();
    }

    public function getAggregation(Table $entityIdsTable, array $dimensions = []): Select
    {
        $aggregationSelect = $this->resource->getConnection()->select();
        $this->addStatusSourceAggregation($aggregationSelect, $entityIdsTable);

        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $aggregationSelect]);

        return $select;
    }

    private function addStatusSourceAggregation(Select $select, Table $table): void
    {
        $select->from(
            ['e' => $this->resource->getTableName('catalog_product_entity')]
        )->joinInner(
            ['entities' => $table->getName()],
            'e.entity_id  = entities.entity_id',
            []
        );

        $this->stockResource->addStockStatusToSelect($select, $this->scopeResolver->getScope()->getWebsite());

        $catalogInventoryTable = $this->stockResource->getMainTable();
        $fromTables = $select->getPart(Select::FROM);

        if ($this->inventoryResolver->isMsiEnabled()
            && $fromTables['stock_status']['tableName'] != $catalogInventoryTable
        ) {
            $stockStatusColumn = 'is_salable';
        } else {
            $stockStatusColumn = 'stock_status';
            $fromTables['stock_status']['joinCondition'] = $this->inventoryResolver->replaceWebsiteWithDefault(
                $fromTables['stock_status']['joinCondition']
            );
            $select->setPart(Select::FROM, $fromTables);
        }

        $select->columns(['value' => 'stock_status.' . $stockStatusColumn]);
    }
}
