<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\ResourceModel;

use Magento\CatalogInventory\Model\Stock;
use Magento\Store\Model\Store;

class GetInStockProductIds extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init($this->getTable('cataloginventory_stock_status'), 'product_id');
    }

    /**
     * @return string[]|int[] [product_id]
     */
    public function execute(array $productIds, int $storeId): array
    {
        $select = $this->getStockSelect()
            ->columns(['product_id'])
            ->where('product_id IN (?)', $productIds)
            ->where('stock_status = ?', Stock::STOCK_IN_STOCK)
            ->where('store_id IN (?)', [$storeId, Store::DEFAULT_STORE_ID]);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @return string[]|int[] [product_id => stock_status]
     */
    public function getStock(int $storeId, array $productIds = []): array
    {
        $select = $this->getStockSelect()
            ->columns(['product_id', 'stock_status'])
            ->where('store_id IN (?)', [$storeId, Store::DEFAULT_STORE_ID]);

        if (!empty($productIds)) {
            $select->where('stock_status.product_id IN (?)', $productIds);
        }

        return $this->getConnection()->fetchPairs($select);
    }

    private function getStockSelect(): \Magento\Framework\DB\Select
    {
        return $this->getConnection()->select()
            ->from(['stock_status' => $this->getTable('cataloginventory_stock_status')], [])
            ->join(
                ['store' => $this->getTable('store')],
                'stock_status.website_id = store.website_id',
                []
            );
    }
}
