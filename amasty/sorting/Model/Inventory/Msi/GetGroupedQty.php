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
use Amasty\Sorting\Model\ResourceModel\Inventory\GetGroupedSimplesQty;
use Amasty\Sorting\Model\ResourceModel\Inventory\GetGroupedSimplesReservationQty;

class GetGroupedQty implements GetQtyInterface
{
    /**
     * @var GetGroupedSimplesQty
     */
    private $getGroupedSimplesQtyResource;

    /**
     * @var GetGroupedSimplesReservationQty
     */
    private $getGroupedSimplesReservationQtyResource;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        GetGroupedSimplesQty $getGroupedSimplesQtyResource,
        GetGroupedSimplesReservationQty $getGroupedSimplesReservationQtyResource,
        Inventory $inventory,
        ConfigProvider $configProvider
    ) {
        $this->getGroupedSimplesQtyResource = $getGroupedSimplesQtyResource;
        $this->getGroupedSimplesReservationQtyResource = $getGroupedSimplesReservationQtyResource;
        $this->inventory = $inventory;
        $this->configProvider = $configProvider;
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
        $simplesQty = $this->getGroupedSimplesQtyResource->execute(
            $sku,
            $websiteCode,
            $this->configProvider->getQtyOutStock()
        );
        $simplesReservationQty = $this->getGroupedSimplesReservationQtyResource->execute(
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
