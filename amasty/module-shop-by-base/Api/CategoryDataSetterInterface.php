<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Api;

use Magento\Catalog\Model\Category;

interface CategoryDataSetterInterface
{
    public const APPLIED_BRAND_VALUE = 'applied_brand_customizer_value';

    public function setCategoryData(Category $category);
}
