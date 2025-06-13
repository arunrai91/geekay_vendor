<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Model;

use Magento\Catalog\Model\Category as MagentoCategory;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;

class Category
{
    /**
     * @param MagentoCategory $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetImageUrl(MagentoCategory $subject, $result)
    {
        if ($subject->hasData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL)) {
            return $subject->getData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL);
        }

        return $result;
    }
}
