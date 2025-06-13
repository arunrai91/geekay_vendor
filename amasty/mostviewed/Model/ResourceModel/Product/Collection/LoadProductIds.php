<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Product\Collection;

use Amasty\Mostviewed\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DB\Select;

class LoadProductIds
{
    /**
     * @return string[]
     */
    public function execute(ProductCollection $productCollection): array
    {
        $clonedProductCollection = clone $productCollection;
        $idsSelect = $clonedProductCollection->getSelect();
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->columns('e.' . $clonedProductCollection->getEntity()->getIdFieldName());
        $clonedProductCollection->renderOrders();
        $idsSelect->limitPage($clonedProductCollection->getCurPage(), $clonedProductCollection->getPageSize());
        $idsSelect->resetJoinLeft();

        return $clonedProductCollection->getConnection()->fetchCol($idsSelect);
    }
}
