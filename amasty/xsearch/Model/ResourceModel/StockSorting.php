<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\ResourceModel;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyCollection;
use Amasty\Xsearch\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection as CatalogCollection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as CatalogSearchCollection;
use Magento\Framework\DB\Select;

class StockSorting
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        Config $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->stockHelper = $stockHelper;
        $this->config = $config;
    }

    /**
     * @param CatalogSearchCollection|ShopbyCollection|CatalogCollection $collection
     * @throws \Zend_Db_Select_Exception
     */
    public function addOutOfStockSortingToCollection($collection): void
    {
        if ($this->isAllowed($collection)) {
            $fromTables = $collection->getSelect()->getPart('from');
            if (!isset($fromTables['stock_status_index'])) {
                $this->stockHelper->addIsInStockFilterToCollection($collection);
                $fromTables = $collection->getSelect()->getPart('from');
            }

            if (isset($fromTables['stock_status_index'])) {
                $stockStatusAlias = $this->moduleManager->isEnabled('Magento_Inventory') &&
                $fromTables['stock_status_index']['tableName'] !=
                $collection->getResource()->getTable('cataloginventory_stock_status')
                    ? 'stock_status_index.is_salable'
                    : 'stock_status_index.stock_status';
            } else {
                $stockStatusAlias = 'is_salable';
            }

            $collection->getSelect()->order(
                $stockStatusAlias . ' ' . CatalogSearchCollection::SORT_ORDER_DESC
            );
            $orders = $collection->getSelect()->getPart(Select::ORDER);
            // move from the last to the the first position
            array_unshift($orders, array_pop($orders));
            $collection->getSelect()->setPart(Select::ORDER, $orders);
        }
    }

    /**
     * @param CatalogSearchCollection|ShopbyCollection|CatalogCollection $collection
     * @return bool
     */
    protected function isAllowed($collection): bool
    {
        return !$collection->isLoaded()
            && $this->getConfig()->isMysqlEngine()
            && $this->getConfig()->isShowOutOfStockLast();
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
