<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Filter\OnSale;

class Helper
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param null $storeId
     * @param bool $filterByCustomerGroup
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addOnSaleFilter(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $storeId = null,
        $filterByCustomerGroup = true
    ) {
        $collection->addPriceData();
        $select = $collection->getSelect();
        if ($collection->getLimitationFilters()->isUsingPriceIndex()) {
            $select->where('price_index.final_price < price_index.price');
        }
    }
}
