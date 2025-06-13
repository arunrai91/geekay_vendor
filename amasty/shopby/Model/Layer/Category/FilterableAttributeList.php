<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Layer\Category;

use Magento\Catalog\Model\Layer\Category\FilterableAttributeList as OrigFilterableAttributeList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;

class FilterableAttributeList extends OrigFilterableAttributeList
{
    /**
     * Add attribute_code sort order to fix case, where position is the same (as in the sample data).
     * And there are different sort orders with the same filters.
     * @return AttributeCollection
     */
    public function getList()
    {
        $collection = $this->collectionFactory->create();
        $collection->setItemObjectClass(Attribute::class)
            ->addStoreLabel($this->storeManager->getStore()->getId())
            ->setOrder('position', 'ASC')
            ->setOrder('attribute_code', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        return $collection;
    }
}
