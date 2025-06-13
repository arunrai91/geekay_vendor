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
    protected $_customizers;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $customizers
     */
    public function __construct(ObjectManagerInterface $objectManager, $customizers = [])
    {
        $this->objectManager = $objectManager;
        $this->_customizers = $customizers;
    }

    /**
     * @param string $customizer
     * @param CatalogCategory $category
     */
    protected function _modifyData($customizer, CatalogCategory $category)
    {
        if (array_key_exists($customizer, $this->_customizers)) {
            $object = $this->objectManager->get($this->_customizers[$customizer]);
            if ($object instanceof Category\CustomizerInterface) {
                /** @var $object  Category\CustomizerInterface */
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
        $this->_modifyData('seo', $category);
        $this->_modifyData('brand', $category);
        $this->_modifyData('page', $category);
        $this->_modifyData('filter', $category);
        $this->_modifyData('seoLast', $category);
    }
}
