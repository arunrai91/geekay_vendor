<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Inventory\Msi;

use Amasty\Sorting\Model\ConfigProvider;
use Amasty\Sorting\Model\Inventory\GetQtyInterface;
use Amasty\Sorting\Model\ResourceModel\Inventory;
use Amasty\Sorting\Model\ResourceModel\Inventory\GetConfigurableQty as GetConfigurableQtyResource;
use Amasty\Sorting\Model\ResourceModel\Inventory\GetConfigurableSimplesQty;
use Amasty\Sorting\Model\ResourceModel\Inventory\GetConfigurableSimplesReservationQty;
use Amasty\Sorting\Model\ResourceModel\Inventory\GetReservationQty as GetReservationQtyResource;
use Magento\Framework\App\ObjectManager;

class GetConfigurableQty implements GetQtyInterface
{
    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var GetConfigurableSimplesQty
     */
    private $getConfigurableSimplesQtyResource;

    /**
     * @var GetConfigurableSimplesReservationQty
     */
    private $getConfigurableSimplesReservationQtyResource;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ?GetConfigurableQtyResource $getConfigurableQtyResource,
        ?GetReservationQtyResource $getReservationQtyResource,
        Inventory $inventory,
        ?GetConfigurableSimplesQty $getConfigurableSimplesQtyResource = null,
        ?GetConfigurableSimplesReservationQty $getConfigurableSimplesReservationQtyResource = null,
        ?ConfigProvider $configProvider = null
    ) {
        $this->inventory = $inventory;
        $this->getConfigurableSimplesQtyResource = $getConfigurableSimplesQtyResource
            ?? ObjectManager::getInstance()->get(GetConfigurableSimplesQty::class);
        $this->getConfigurableSimplesReservationQtyResource = $getConfigurableSimplesReservationQtyResource
            ?? ObjectManager::getInstance()->get(GetConfigurableSimplesReservationQty::class);
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    /**
     * Qty with reservation qty.
     *
     * @param string $sku
     * @param string $websiteCode
     * @return null|float
     *
     * @see \Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity::execute
     */
    public function execute(string $sku, string $websiteCode): ?float
    {
        $simplesQty = $this->getConfigurableSimplesQtyResource->execute(
            $sku,
            $websiteCode,
            $this->configProvider->getQtyOutStock()
        );
        $simplesReservationQty = $this->getConfigurableSimplesReservationQtyResource->execute(
            $sku,
            $this->inventory->getStockId($websiteCode)
        );

        foreach ($simplesReservationQty as $sku => $reservationQty) {
            if (!isset($simplesQty[$sku])) {
                continue;
            }
            $simplesQty[$sku] += $reservationQty;
        }

        return array_sum(array_filter($simplesQty, function ($value) {
            return $value > 0;
        }));
    }
}
