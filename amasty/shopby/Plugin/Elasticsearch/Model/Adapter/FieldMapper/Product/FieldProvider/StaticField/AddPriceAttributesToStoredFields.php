<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * It is necessary to add attributes with price type to stored fields, because if the dynamics price algorithm is
 * used, magento algorithm makes a request to the elastic for products with a field for which it generates
 * ranges, but since magento adds only native price attribute to stored fields an error occurs
 */
class AddPriceAttributesToStoredFields
{
    private const PRICE_INPUT_TYPE = 'price';

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    public function __construct(
        AttributeProvider $attributeAdapterProvider,
        ResolverInterface $fieldNameResolver
    ) {
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * @see StaticField::getField()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetField(StaticField $subject, array $result, AbstractAttribute $attribute): array
    {
        if ($result && $attribute->getIsFilterable() && $attribute->getFrontendInput() === self::PRICE_INPUT_TYPE) {
            $attributeAdapter = $this->attributeAdapterProvider->getByAttributeCode($attribute->getAttributeCode());
            $fieldName = $this->fieldNameResolver->getFieldName($attributeAdapter);
            $result[$fieldName]['store'] = true;
        }

        return $result;
    }
}
