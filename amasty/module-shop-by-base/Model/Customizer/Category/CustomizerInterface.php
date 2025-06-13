<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */
namespace Amasty\ShopbyBase\Model\Customizer\Category;

use Magento\Catalog\Model\Category;

interface CustomizerInterface
{
    public function prepareData(Category $category);
}
