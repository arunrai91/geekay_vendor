<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Customizer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Model\Category as CatalogCategory;

class Category
{
    /**
     * Key for store original category url in category object before replace with canonical.
     */
    public const ORIGINAL_CATEGORY_URL = 'original_url';

    /**
     * @var array
     */
    private array $customizers;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager, array $customizers = [])
    {
        $this->objectManager = $objectManager;
        $this->customizers = $customizers;
    }

    /**
     * @param string $customizer
     * @param CatalogCategory $category
     */
    private function modifyData($customizer, CatalogCategory $category)
    {
        if (array_key_exists($customizer, $this->customizers)) {
            $object = $this->objectManager->get($this->customizers[$customizer]);
            if ($object instanceof Category\CustomizerInterface) {
                /** @var Category\CustomizerInterface $object */
                $object->prepareData($category);
            }
        }
    }

    /**
     * @param CatalogCategory $category
     * @see \Amasty\ShopbyBase\Plugin\Catalog\Block\Category\View::afterGetCurrentCategory
     */
    public function prepareData(CatalogCategory $category)
    {
        $this->modifyData('seo', $category);
        $this->modifyData('brand', $category);
        $this->modifyData('page', $category);
        $this->modifyData('filter', $category);
        $this->modifyData('seoLast', $category);
    }
}
