<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Indexer\Stock;

use Magento\CatalogInventory\Model\Configuration;
use Magento\ConfigurableProduct\Model\ResourceModel\Indexer\Stock\Configurable as NativeIndexer;
use Zend_Db_Expr;
use Magento\Framework\DB\Select;

class Configurable extends NativeIndexer
{
    /**
     * @inheritdoc
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $select = parent::_getStockStatusSelect($entityIds, $usePrimaryTable);
        $this->calculateDependOnSimples($select);

        return $select;
    }

    /**
     * @param Select $select
     */
    private function calculateDependOnSimples($select)
    {
        $globalMinQty = $this->_scopeConfig->getValue(Configuration::XML_PATH_MIN_QTY);

        $select->joinInner(
            ['lcisi' => $this->getTable('cataloginventory_stock_item')],
            'lcisi.stock_id = cis.stock_id AND lcisi.product_id = l.product_id',
            []
        ); // join stock_item for simple products

        $connection = $select->getConnection();

        $columns = $select->getPart(Select::COLUMNS);
        foreach ($columns as &$column) {
            if (isset($column[2]) && $column[2] == 'qty') {
                $qtyExpression = $connection->getCheckSql(
                    'i.stock_status > 0',
                    $connection->getCheckSql(
                        'lcisi.use_config_min_qty = 1',
                        sprintf('i.qty - %f', $globalMinQty),
                        'i.qty - lcisi.min_qty'
                    ),
                    0
                );
                $column[1] = new Zend_Db_Expr(sprintf(
                    'SUM(%s)',
                    $connection->getGreatestSql([$qtyExpression, 0])
                ));
            }
            // determine stock status based on simples
//            if (isset($column[2]) && $column[2] == 'status') {
//                $column[1] = new Zend_Db_Expr('MAX(i.stock_status = 1)');
//            }
        }
        $select->setPart(Select::COLUMNS, $columns);
    }
}
