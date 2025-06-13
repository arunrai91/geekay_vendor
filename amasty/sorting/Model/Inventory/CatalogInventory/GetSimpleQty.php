<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Inventory\CatalogInventory;

use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\Inventory\GetQtyInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ObjectManager;

class GetSimpleQty implements GetQtyInterface
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(StockRegistryInterface $stockRegistry, ?ConfigProvider $configProvider = null)
    {
        $this->stockRegistry = $stockRegistry;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    public function execute(string $sku, string $websiteCode): ?float
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku, $websiteCode);
        if (!$stockItem->getIsInStock()) {
            return null;
        }

        $qty = $stockItem->getQty();
        if ($qty === null) {
            return null;
        }

        if ($stockItem->getUseConfigMinQty()) {
            return $qty - $this->configProvider->getQtyOutStock();
        } else {
            return $qty - $stockItem->getMinQty();
        }
    }
}
