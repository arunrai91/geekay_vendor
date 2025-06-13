<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Seo for Magento 2 (System)
 */

namespace Amasty\ShopbySeo\Model\SeoOptionsModifier;

interface SeoModifierInterface
{
    /**
     * Method modifies seo attributes options data
     *
     * @param array $optionsSeoData array($storeId => [$attributeCode => [$optionId => $alias]]
     * @param int $storeId
     * @param array $attributeIds
     * @return void
     */
    public function modify(array &$optionsSeoData, int $storeId, array &$attributeIds = []): void;
}
