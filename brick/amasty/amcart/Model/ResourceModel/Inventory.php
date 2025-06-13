<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model\ResourceModel;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager;

class Inventory
{
    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var bool
     */
    private $msiEnabled = null;

    /**
     * @var array
     */
    private $stockStatus;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ResourceConnection
     */
    private $connection;

    public function __construct(
        StockRegistryInterface $stockRegistry,
        ResourceConnection $connection,
        Manager $moduleManager
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->connection = $connection;
        $this->msiEnabled = $moduleManager->isEnabled('Magento_Inventory');
    }

    public function getStockStatus(string $productSku, string $websiteCode): int
    {
        if (!isset($this->stockStatus[$websiteCode][$productSku])) {
            if ($this->msiEnabled && $this->getStockId($websiteCode) !== Stock::DEFAULT_STOCK_ID) {
                $result = $this->getMsiSalable($productSku, $websiteCode);
            } else {
                $result = (int)$this->stockRegistry->getStockItemBySku($productSku)->getIsInStock();
            }

            $this->stockStatus[$websiteCode][$productSku] = $result;
        }

        return $this->stockStatus[$websiteCode][$productSku];
    }

    private function getMsiSalable(string $productSku, string $websiteCode): int
    {
        $select = $this->connection->getConnection()
            ->select()
            ->from(
                [$this->connection->getTableName(
                    sprintf('inventory_stock_%d', $this->getStockId($websiteCode))
                )],
                ['is_salable']
            )->where('sku = (?)', $productSku);

        return (int)$this->connection->getConnection()->fetchOne($select);
    }

    private function getStockId(string $websiteCode): int
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->connection->getConnection()->select()
                ->from($this->connection->getTableName('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int)$this->connection->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }
}
