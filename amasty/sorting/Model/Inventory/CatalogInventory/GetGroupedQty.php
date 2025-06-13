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
use Amasty\Sorting\Model\ResourceModel\CatalogInventory\GetGroupedQty as GetGroupedQtyResource;

class GetGroupedQty implements GetQtyInterface
{
    /**
     * @var GetGroupedQtyResource
     */
    private $getGroupedQtyResource;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(GetGroupedQtyResource $getGroupedQtyResource, ConfigProvider $configProvider)
    {
        $this->getGroupedQtyResource = $getGroupedQtyResource;
        $this->configProvider = $configProvider;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, string $websiteCode): ?float
    {
        return $this->getGroupedQtyResource->execute($sku, $this->configProvider->getQtyOutStock());
    }
}
