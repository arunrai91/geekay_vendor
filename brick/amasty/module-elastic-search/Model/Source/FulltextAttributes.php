<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Source;

use Amasty\ElasticSearch\Model\GetNonTextAttributes;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class FulltextAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var GetNonTextAttributes
     */
    private $getNonTextAttributes;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var array
     */
    private $fulltextAttributes;

    public function __construct(
        GetNonTextAttributes $getNonTextAttributes,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->getNonTextAttributes = $getNonTextAttributes;
        $this->attributeCollection = $attributeCollectionFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $attributes = [];
        foreach ($this->toArray() as $code => $label) {
            $attributes[] = ['value' => $code, 'label' => $label];
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->fulltextAttributes === null) {
            $this->fulltextAttributes = [];
            /** @var \Magento\Eav\Model\Attribute $attribute */
            $this->attributeCollection->addIsSearchableFilter()
                ->setAttributesExcludeFilter($this->getNonTextAttributes->execute());
            foreach ($this->attributeCollection as $attribute) {
                $this->fulltextAttributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        return $this->fulltextAttributes;
    }
}
