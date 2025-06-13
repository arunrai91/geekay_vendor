<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source\ChildrenCategoriesBlock;

use Amasty\Base\Model\Source\Category;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * @deprecated replaced with optimized Class with Cache
 * @see \Amasty\Shopby\Model\Source\Category
 */
class Categories implements ArrayInterface
{
    public const ALL_CATEGORIES = 0;
    public const SYSTEM_CATEGORY_ID = 1;
    public const ROOT_LEVEL = 1;

    /**
     * @var Category
     */
    private $source;

    public function __construct(
        ?CollectionFactory $collectionFactory,
        Category $source = null
    ) {
        $this->source = $source ?? ObjectManager::getInstance()->get(Category::class);
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->source->toOptionArray();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->source->toArray();
    }
}
