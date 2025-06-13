<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Inventory;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\ResourceModel\GetInStockProductIds;
use Amasty\Shopby\Model\ResourceModel\GetMsiInStockProductIds;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager;

class Resolver
{
    public const WEBSITE_CONDITION_REGEXP = '@(`?website_id`?=\s*)\d+@';

    public const DEFAULT_WEBSITE_ID = 0;

    /**
     * @var Manager
     */
    private $moduleManager;
    /**
     * @var GetInStockProductIds
     */
    private $getInStockProductIds;

    /**
     * @var GetMsiInStockProductIds
     */
    private $getMsiInStockProductIds;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Manager $moduleManager,
        GetInStockProductIds $getInStockProductIds,
        GetMsiInStockProductIds $getMsiInStockProductIds,
        ConfigProvider $configProvider = null // TODO not optional
    ) {
        $this->moduleManager = $moduleManager;
        $this->getInStockProductIds = $getInStockProductIds;
        $this->getMsiInStockProductIds = $getMsiInStockProductIds;
        // OM is temporary for backward compatibility
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    /**
     * @return string[]|int[] [product_id]
     */
    public function getInStockProducts(array $productIds, int $storeId): array
    {
        if ($this->isMsiEnabled()) {
            if ($this->configProvider->isStockByReservedQty($storeId)) {
                return $this->getMsiInStockProductIds->filterIsStockWithReservation(
                    $productIds,
                    $storeId,
                    $this->configProvider->getCatalogManageStock(),
                    $this->configProvider->getCatalogMinQty()
                );
            }

            return $this->getMsiInStockProductIds->execute($productIds, $storeId);
        }

        return $this->getInStockProductIds->execute($productIds, $storeId);
    }

    /**
     * @param int $storeId
     * @param int[] $productIds
     * @return string[]|int[] [product_id => stock_status]
     */
    public function getProductStock(int $storeId, array $productIds = []): array
    {
        if ($this->isMsiEnabled()) {
            if ($this->configProvider->isStockByReservedQty($storeId)) {
                return $this->getMsiInStockProductIds->getStockStatusWithReservation(
                    $storeId,
                    $this->configProvider->getCatalogManageStock(),
                    $this->configProvider->getCatalogMinQty(),
                    $productIds
                );
            }

            return $this->getMsiInStockProductIds->getStockStatus($storeId, $productIds);
        }

        return $this->getInStockProductIds->getStock($storeId, $productIds);
    }

    /**
     * @return bool
     */
    public function isMsiEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }

    /**
     * @param string $websiteCondition
     * @return string
     */
    public function replaceWebsiteWithDefault(string $websiteCondition): string
    {
        return preg_replace(
            self::WEBSITE_CONDITION_REGEXP,
            '$1 ' . self::DEFAULT_WEBSITE_ID,
            $websiteCondition
        );
    }
}
