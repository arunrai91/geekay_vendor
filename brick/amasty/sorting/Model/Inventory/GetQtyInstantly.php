<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Inventory;

use Magento\Framework\Module\Manager as ModuleManager;

class GetQtyInstantly implements GetQtyInterface
{
    /**
     * @var GetQtyInterface
     */
    private $getCatalogInventoryQtyByType;

    /**
     * @var GetQtyInterface
     */
    private $getMsiQtyByType;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        GetQtyInterface $getCatalogInventoryQtyByType,
        GetQtyInterface $getMsiQtyByType,
        ModuleManager $moduleManager
    ) {
        $this->getCatalogInventoryQtyByType = $getCatalogInventoryQtyByType;
        $this->getMsiQtyByType = $getMsiQtyByType;
        $this->moduleManager = $moduleManager;
    }

    public function execute(string $sku, string $websiteCode): ?float
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $qty = $this->getMsiQtyByType->execute($sku, $websiteCode);
        } else {
            $qty = $this->getCatalogInventoryQtyByType->execute($sku, $websiteCode);
        }

        return $qty;
    }
}
