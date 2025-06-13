<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Sorting for Magento 2
 */

namespace Amasty\Sorting\Model\Inventory;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager as ModuleManager;

class GetQty implements GetQtyInterface
{
    /**
     *
     * @var array Array [
     *  'website_code' => [
     *      'sku' => 'qty'
     *      ...
     *  ]
     *  ...
     * ]
     */
    private $qty = [];

    /**
     * @var GetQtyInstantly
     */
    private $getQtyInstantly;

    public function __construct(
        ?GetQtyInterface $getCatalogInventoryQtyByType,
        ?GetQtyInterface $getMsiQtyByType,
        ?ModuleManager $moduleManager,
        ?GetQtyInstantly $getQtyInstantly = null
    ) {
        $this->getQtyInstantly = $getQtyInstantly ?? ObjectManager::getInstance()->get(GetQtyInstantly::class);
    }

    /**
     * @param string $sku
     * @param string $websiteCode
     * @return null|float
     */
    public function execute(string $sku, string $websiteCode): ?float
    {
        if (!isset($this->qty[$websiteCode][$sku])) {
            $this->qty[$websiteCode][$sku] = $this->getQtyInstantly->execute($sku, $websiteCode);
        }

        return $this->qty[$websiteCode][$sku];
    }
}
