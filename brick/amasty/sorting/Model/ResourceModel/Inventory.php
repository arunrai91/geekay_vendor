<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;

class Inventory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var array
     */
    private $sourceCodes;

    /**
     * @var array
     */
    private $stockStatus;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var bool
     */
    private $isCacheable;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null,
        bool $isCacheable = true
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->isCacheable = $isCacheable;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->stockIds = [];
        $this->sourceCodes = [];
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatus($productSku, $websiteCode)
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $stockStatus = $this->getMsiStock($productSku, $websiteCode);
        } else {
            $stockStatus = $this->getStockStatusObject($productSku, $websiteCode)->getStockStatus();
        }

        return $stockStatus;
    }

    /**
     * Get stock status object for case when MSI disabled
     * (use cataloginventory_stock_status table)
     */
    private function getStockStatusObject(string $productSku, string $websiteCode): StockStatusInterface
    {
        return $this->stockRegistry->getStockStatusBySku($productSku, $websiteCode);
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStockItem($productSku, $websiteCode)
    {
        return $this->stockRegistry->getStockItemBySku($productSku, $websiteCode);
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMsiStock($productSku, $websiteCode)
    {
        if ($this->isCacheable && !isset($this->stockStatus[$websiteCode][$productSku])) {
            $this->stockStatus[$websiteCode][$productSku]
                = $this->fetchMsiStock((string)$productSku, (string)$websiteCode);
        }

        return $this->isCacheable
            ? $this->stockStatus[$websiteCode][$productSku]
            : $this->fetchMsiStock((string)$productSku, (string)$websiteCode);
    }

    private function fetchMsiStock(string $productSku, string $websiteCode): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_stock_' . $this->getStockId($websiteCode)), ['is_salable'])
            ->where('sku = ?', $productSku)
            ->group('sku');

        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return int
     */
    public function getStockId(string $websiteCode): int
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int)$this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return array
     */
    public function getSourceCodes($websiteCode)
    {
        if (!isset($this->sourceCodes[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $this->getStockId($websiteCode));

            $this->sourceCodes[$websiteCode] = $this->getConnection()->fetchCol($select);
        }

        return $this->sourceCodes[$websiteCode];
    }
}
