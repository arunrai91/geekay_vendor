<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Source;

use Amasty\Base\Model\Source\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManager;

/**
 * @deprecated replaced with optimized Class with Cache
 * @see \Amasty\Shopby\Model\Source\Category
 */
class Category
{
    public const SYSTEM_CATEGORY_ID = 1;
    public const ROOT_LEVEL = 1;

    /**
     * @var bool
     */
    private $emptyOption = true;

    /**
     * @var CategoryFactory
     */
    private $categorySourceFactory;

    public function __construct(
        ?CollectionFactory $collectionFactory, //@deprecated
        ?StoreManager $storeManager, //@deprecated
        CategoryFactory $category = null
    ) {
        $this->categorySourceFactory = $category ?? ObjectManager::getInstance()
            ->get(CategoryFactory::class);
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $source = $this->categorySourceFactory->create(['caption' => $this->emptyOption ? ' ' : null]);

        return $source->toArray();
    }

    /**
     * @param bool $emptyOption
     * @return $this
     */
    public function setEmptyOption($emptyOption)
    {
        $this->emptyOption = $emptyOption;

        return $this;
    }
}
