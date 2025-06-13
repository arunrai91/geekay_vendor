<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute;

class BrandAttribute implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptions();
    }

    private function getOptions()
    {
        $collection = $this->collectionFactory->create();
        $collection->addIsFilterableFilter();
        $collection->addOrder('attribute_code', 'asc');

        $options = ['' => __('-- Empty --')];
        foreach ($collection->getItems() as $attribute) {
            /** @var Attribute $attribute */
            $options[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
        }

        return $options;
    }
}
