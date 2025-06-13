<?php

/**
 * Product:       Xtento_OrderExport
 * ID:            %!uniqueid!%
 * Last Modified: 2018-10-09T09:27:15+00:00
 * File:          Model/Export/Condition/Product.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Condition;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
        $this->setType('Xtento\OrderExport\Model\Export\Condition\Product');
    }

    /**
     * Load attribute options
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!method_exists($attribute, 'isAllowedForRuleCondition') || !$attribute->isAllowedForRuleCondition()
                /* || !$attribute->getDataUsingMethod($this->_isUsedForRuleProperty)*/
            ) {
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $product = $this->_productFactory->create()->setStoreId($object->getStoreId())->load($object->getProductId());
        $this->_entityAttributeValues[$product->getId()][$product->getStoreId()] = $product->getData(
            $this->getAttribute()
        );
        #var_dump($this->getAttribute(), $product->getData($this->getAttribute()), parent::validateAttribute($product));
        return parent::validate($product);
    }
}
