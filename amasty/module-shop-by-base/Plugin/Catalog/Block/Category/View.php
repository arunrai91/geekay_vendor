<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Plugin\Catalog\Block\Category;

use Magento\Catalog\Block\Category\View as CategoryViewBlock;
use Magento\Catalog\Model\Category;
use Amasty\ShopbyBase\Model\Customizer\CategoryFactory as CustomizerCategoryFactory;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;

class View
{
    /**
     * @var CustomizerCategoryFactory
     */
    private $customizerCategoryFactory;

    /**
     * @var bool
     */
    private $categoryModified = false;

    /**
     * @param CustomizerCategoryFactory $customizerCategoryFactory
     */
    public function __construct(
        CustomizerCategoryFactory $customizerCategoryFactory
    ) {
        $this->customizerCategoryFactory = $customizerCategoryFactory;
    }

    /**
     * @param CategoryViewBlock $subject
     * @param Category $category
     * @return Category
     * @see \Magento\Catalog\Block\Category\View::getCurrentCategory
     */
    public function afterGetCurrentCategory(CategoryViewBlock $subject, $category)
    {
        if ($category instanceof Category && !$this->categoryModified) {
            /** @var \Amasty\ShopbyBase\Model\Customizer\Category $customizer */
            $customizer = $this->customizerCategoryFactory->create();
            $customizer->prepareData($category);

            $this->categoryModified = true;
        }
        return $category;
    }

    /**
     * @param CategoryViewBlock $subject
     * @param bool $isMixedMode
     * @return bool
     */
    public function afterIsMixedMode(CategoryViewBlock $subject, $isMixedMode)
    {
        if (!$isMixedMode) {
            if ($subject->getCurrentCategory()->getData(CategoryManager::CATEGORY_FORCE_MIXED_MODE)) {
                $isMixedMode = true;
            }
        }

        return $isMixedMode;
    }
}
