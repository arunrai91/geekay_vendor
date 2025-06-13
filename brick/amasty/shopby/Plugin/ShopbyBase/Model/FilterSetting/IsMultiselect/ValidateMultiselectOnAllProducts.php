<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\ShopbyBase\Model\FilterSetting\IsMultiselect;

use Amasty\Shopby\Model\Request\IsAllProductsRegistry;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use Amasty\ShopbyBrand\Model\ConfigProvider as BrandConfigProvider;

class ValidateMultiselectOnAllProducts
{
    /**
     * @var BrandConfigProvider
     */
    private BrandConfigProvider $brandConfigProvider;

    /**
     * @var IsAllProductsRegistry
     */
    private IsAllProductsRegistry $isAllProductsRegistry;

    public function __construct(BrandConfigProvider $brandConfigProvider, IsAllProductsRegistry $isAllProductsRegistry)
    {
        $this->brandConfigProvider = $brandConfigProvider;
        $this->isAllProductsRegistry = $isAllProductsRegistry;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(IsMultiselect $subject, bool $result, ?string $attributeCode): bool
    {
        if (!$result) {
            return false;
        }

        $isBrandOnAllProducts = $attributeCode === $this->brandConfigProvider->getBrandAttributeCode()
            && $this->isAllProductsRegistry->isAllProducts();

        return !$isBrandOnAllProducts;
    }
}
