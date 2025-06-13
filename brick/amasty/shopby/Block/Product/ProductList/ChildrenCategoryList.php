<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Product\ProductList;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\View\Element\Template;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Source\ChildrenCategoriesBlock\DisplayMode;

class ChildrenCategoryList extends Template
{
    public const DEFAULT_SLIDES_COUNT = 5;
    public const DEFAULT_IMAGE_SIZE = 100;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Shopby\Helper\Category
     */
    private $categoryHelper;

    /**
     * @var Category[]
     */
    private $childrenCategories = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;

    /**
     * @var array
     */
    private $availableModules = ['catalog', 'catalogsearch'];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Shopby\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $coreRegistry;
        $this->categoryHelper = $categoryHelper;
        $this->categoryCollection = $categoryCollection;
        $this->request = $context->getRequest();
        $this->configProvider = $configProvider;
    }

    /**
     * @param Category $category
     * @param int $size
     *
     * @return bool|string|null
     */
    public function getCategoryImageUrl(Category $category, $size)
    {
        return $this->categoryHelper->getImageUrl(
            $category->getThumbnail() ?: $category->getImage(),
            true,
            $size,
            $size
        );
    }

    /**
     * @return array
     */
    public function getChildrenCategories()
    {
        if (empty($this->childrenCategories)) {
            $currentCategory = $this->getCurrentCategory();
            $collection = $currentCategory->getChildrenCategories();

            if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {

                /**
                 * @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection
                 */
                $collection->setLoadProductCount(true);
                $collection->addNameToResult();
                $collection->addOrderField('name');
                $collection->addAttributeToSelect('image');
                $collection->addAttributeToSelect('thumbnail');
                $collection->addIsActiveFilter();
                if ($this->getData('attributes_to_select')) {
                    $collection->addAttributeToSelect($this->getData('attributes_to_select'));
                }
            } elseif (is_array($collection) && count($collection)) {
                $this->categoryCollection->loadProductCount($collection);
            }

            foreach ($collection as $category) {
                if ($category->getData('product_count') || $category->getProductCount()) {
                    $this->childrenCategories[] = $category;
                }
            }
        }

        return $this->childrenCategories;
    }

    /**
     * @return int
     */
    public function getItemsCountPerSlide()
    {
        return $this->categoryHelper->getChildrenCategoriesItemsCountPerSlide() ?: self::DEFAULT_SLIDES_COUNT;
    }

    /**
     * @return int
     */
    public function getImageSize()
    {
        return $this->categoryHelper->getChildrenCategoriesBlockImageSize();
    }

    /**
     * @return bool
     */
    public function showLabels()
    {
        return $this->categoryHelper->showChildrenCategoriesImageLabels();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->categoryHelper->getChildrenCategoriesBlockDisplayMode()
            && in_array($this->request->getModuleName(), $this->availableModules)
            && $this->isAllowInCategory()
        ) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    private function isAllowInCategory()
    {
        $currentCategoryId = $this->getCurrentCategory()->getId();
        $allowCategories = $this->categoryHelper->getAllowCategories() ?? '';

        return in_array($currentCategoryId, explode(',', $allowCategories))
            || $allowCategories == \Amasty\Base\Model\Source\Category::EMPTY_OPTION_ID;
    }

    /**
     * @return bool
     */
    public function isOnlyLabels()
    {
        return $this->categoryHelper->getChildrenCategoriesBlockDisplayMode() == DisplayMode::LABELS;
    }

    /**
     * @return bool
     */
    public function isSliderEnabled()
    {
        return $this->categoryHelper->isChildrenCategoriesSliderEnabled()
            && count($this->getChildrenCategories()) > $this->getItemsCountPerSlide();
    }

    /**
     * @return Category
     */
    public function getCurrentCategory(): CategoryInterface
    {
        return $this->registry->registry('current_category');
    }

    public function isCategoryInfinityLoop(): bool
    {
        return $this->configProvider->isCategoryInfinityLoop()
            && count($this->getChildrenCategories()) >= $this->getItemsCountPerSlide() * 2;
    }
}
