<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data\Product;

class AttributeDataProvider
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute[]
     */
    private $attributesById;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttributeCollectionFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->productAttributeCollectionFactory = $prodAttributeCollectionFactory;
    }

    /**
     * @param string $backendType
     * @return $this
     */
    private function initSearchableAttributes()
    {
        if ($this->attributesById === null) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
            $productAttributes = $this->productAttributeCollectionFactory->create();
            $productAttributes->addToIndexFilter(true);

            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $productAttributes->getItems();

            $entity = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntity();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
                $this->attributesById[$attribute->getAttributeId()] = $attribute;
            }
        }

        return $this;
    }

    /**
     * @param int|string $attribute
     * @return \Magento\Eav\Model\Entity\Attribute|null
     */
    public function getAttribute($attributeId)
    {
        $this->initSearchableAttributes();

        if (isset($this->attributesById[$attributeId])) {
            return $this->attributesById[$attributeId];
        }

        return null;
    }
}
