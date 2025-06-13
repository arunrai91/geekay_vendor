<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Add to $useDefaults array values from restricted attributes
 * that not show on frontend
 */
class ModifyUseDefaultArrayWithValuesFromRestrictAttributes
{
    /**
     * @var Data $helper
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param AttributeFilter $subject
     * @param Product $product
     * @param array $productData
     * @param array $useDefaults
     * @return array
     */
    public function beforePrepareProductAttributes(
        $subject,
        Product $product,
        array $productData,
        array $useDefaults
    ): array {
        $currentRule = $this->helper->currentRule();

        if ($currentRule && $currentRule->getAttributes() && $useDefaults) {
            $allProductAttributes = $product->getAttributes();

            $useDefaultValuesForProductAttributes = [];
            foreach ($allProductAttributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();

                if ($attribute->getIsGlobal() != ScopedAttributeInterface::SCOPE_GLOBAL) {
                    $useDefaultValuesForProductAttributes[$attributeCode] =
                        $this->transformBoolToString($this->usedDefault($product, $attributeCode));
                }
            }

            $useDefaults = array_merge($useDefaultValuesForProductAttributes, $useDefaults);
        }

        return [$product, $productData, $useDefaults];
    }

    private function usedDefault(Product $product, string $attributeCode): bool
    {
        $defaultValue = $product->getAttributeDefaultValue($attributeCode);

        if (!$product->getExistsStoreValueFlag($attributeCode)) {
            return true;
        }

        return $defaultValue === false;
    }

    private function transformBoolToString(bool $boolValue): string
    {
        return $boolValue ? '1' : '0';
    }
}
