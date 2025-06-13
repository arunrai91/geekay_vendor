<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Permissions for Magento 2
 */

namespace Amasty\Rolepermissions\Plugin\Catalog\Model\Product\TypeTransitionManager;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TypeTransitionManager;

class PreventTypeChange
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * We limit the call of the Magento's original method due to the fact
     * that the replacement of the product type for the "virtual" type
     * should not occur when lacking the access to the "weight" attribute.
     */
    public function aroundProcessProduct(
        TypeTransitionManager $subject,
        callable $proceed,
        Product $product
    ): void {
        $allowedAttributeCodes = $this->helper->getAllowedAttributeCodes();

        if (is_bool($allowedAttributeCodes)
            || in_array(ProductAttributeInterface::CODE_WEIGHT, $allowedAttributeCodes)
        ) {
            $proceed($product);
        }
    }
}
