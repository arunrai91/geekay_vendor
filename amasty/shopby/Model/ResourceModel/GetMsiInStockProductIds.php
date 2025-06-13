<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\ResourceModel;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Model\ResourceModel\Db\Context;

class GetMsiInStockProductIds extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var int[]
     */
    private $stockIds = [];

    /**
     * @var array
     */
    private $compositeProductTypes;

    public function __construct(
        Context $context,
        $connectionName = null,
        $compositeProductTypes = []
    ) {
        parent::__construct($context, $connectionName);

        $this->compositeProductTypes = $compositeProductTypes;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->stockIds = [];
    }

    /**
     * @return string[]|int[] [product_id]
     */
    public function execute(array $productIds, int $storeId): array
    {
        $select = $this->getStockQuery($this->getStockId($storeId))
            ->columns(['cpe.entity_id'])
            ->where('cpe.entity_id IN (?)', $productIds)
            ->where('`is`.is_salable = ?', Stock::STOCK_IN_STOCK);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @return string[]|int[] [product_id]
     */
    public function filterIsStockWithReservation(
        array $productIds,
        int $storeId,
        int $configMangeStock,
        float $configMinQty
    ): array {
        $adapter = $this->getConnection();
        $stockId = $this->getStockId($storeId);
        $select = $this->getStockQuery($stockId)
            ->columns(['cpe.entity_id'])
            ->joinInner(
                ['csi' => $this->getTable('cataloginventory_stock_item')],
                'csi.product_id = cpe.entity_id',
                []
            )
            ->where('cpe.entity_id IN (?)', $productIds);

        $stockExpression = $this->processStockExpression($stockId, $select);

        $select->where($stockExpression);

        return $adapter->fetchCol(
            $select,
            [
                ':config_manage_stock' => $configMangeStock,
                ':config_min_qty' => $configMinQty
            ]
        );
    }

    /**
     * @return string[]|int[] [product_id => stock_status]
     */
    public function getStockStatusWithReservation(
        int $storeId,
        int $configMangeStock,
        float $configMinQty,
        array $productIds = []
    ): array {
        $stockId = $this->getStockId($storeId);
        $selectSimpleProducts = $this->getStockQuery($stockId);
        $selectCompositeProducts = clone $selectSimpleProducts;
        $selectSimpleProducts->joinInner(
            ['csi' => $this->getTable('cataloginventory_stock_item')],
            'csi.product_id = cpe.entity_id',
            []
        );

        $stockExpression = $this->processStockExpression($stockId, $selectSimpleProducts);

        $selectSimpleProducts->columns(['cpe.entity_id', 'is_qty_salable' => $stockExpression])
            ->where('cpe.type_id NOT IN (?)', array_values($this->compositeProductTypes));
        $selectCompositeProducts->columns(['cpe.entity_id', 'is_qty_salable' => 'is.is_salable'])
            ->where('cpe.type_id IN (?)', array_values($this->compositeProductTypes));
        if (!empty($productIds)) {
            $selectSimpleProducts->where('cpe.entity_id IN (?)', $productIds);
            $selectCompositeProducts->where('cpe.entity_id IN (?)', $productIds);
        }

        return $this->getConnection()->fetchPairs(
            $this->getConnection()->select()->union([$selectSimpleProducts, $selectCompositeProducts]),
            [
                ':config_manage_stock' => $configMangeStock,
                ':config_min_qty' => $configMinQty,
            ]
        );
    }

    /**
     * @return string[]|int[] [product_id => stock_status]
     */
    public function getStockStatus(int $storeId, array $productIds = []): array
    {
        $stockId = $this->getStockId($storeId);
        $adapter = $this->getConnection();
        $select = $this->getStockQuery($stockId);
        $select->columns(['cpe.entity_id', 'is.is_salable']);

        if (!empty($productIds)) {
            $select->where('cpe.entity_id IN (?)', $productIds);
        }

        return $adapter->fetchPairs($select);
    }

    private function getStockQuery(int $stockId): \Magento\Framework\DB\Select
    {
        $adapter = $this->getConnection();
        return $adapter->select()
            ->from(
                ['cpe' => $this->getTable('catalog_product_entity')],
                []
            )
            ->joinInner(
                ['is' => $this->getTable('inventory_stock_' . $stockId)],
                'cpe.sku = `is`.sku',
                []
            );
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getStockId(int $storeId): int
    {
        if (!isset($this->stockIds[$storeId])) {
            $select = $this->getConnection()->select()
                ->from(['stock' => $this->getTable('inventory_stock_sales_channel')], ['stock_id'])
                ->join(
                    ['store_website' => $this->getTable('store_website')],
                    'store_website.code = stock.code',
                    []
                )
                ->join(
                    ['store' => $this->getTable('store')],
                    'store.website_id = store_website.website_id',
                    []
                )
                ->where('store_id = ?', $storeId)
                ->where('stock.type = \'website\'');

            $this->stockIds[$storeId] = (int)$this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$storeId];
    }

    /**
     * @return \Zend_Db_Expr
     */
    private function processStockExpression(int $stockId, \Magento\Framework\DB\Select $select)
    {
        $adapter = $this->getConnection();
        $reservationSbSelect = $adapter->select()
            ->from($this->getTable('inventory_reservation'), ['sku', 'reserved_qty' => 'SUM(quantity)'])
            ->where('stock_id = ?', $stockId)
            ->group('sku');

        $select->joinLeft(
            ['ir' => $reservationSbSelect],
            'ir.sku = cpe.sku',
            []
        );

        $reservedQty = $adapter->getIfNullSql('reserved_qty', 0);
        $manageStock = $adapter->getCheckSql('csi.use_config_manage_stock', ':config_manage_stock', 'csi.manage_stock');
        $minQty = $adapter->getCheckSql('csi.use_config_min_qty', ':config_min_qty', 'csi.min_qty');

        $stockExpression = $adapter->getCheckSql(
            '`is`.is_salable', // if already out of stock, then no need to check reservation
            $adapter->getCheckSql(
                $manageStock,
                sprintf('(`is`.quantity + %s) > %s', $reservedQty, $minQty),
                Stock::STOCK_IN_STOCK
            ),
            Stock::STOCK_OUT_OF_STOCK
        );

        return $stockExpression;
    }
}
