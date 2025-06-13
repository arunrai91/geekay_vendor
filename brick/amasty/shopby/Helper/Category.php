<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Helper;

use Amasty\Shopby\Model\Source\RenderCategoriesLevel;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Category\Manager;
use Amasty\ShopbyBase\Model\FilterSetting\IsMultiselect;
use GuzzleHttp\Promise\Is;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Amasty\Shopby\Model\Category\Attribute\Frontend\Image as ImageModel;
use Amasty\Shopby\Plugin\Catalog\Model\Category as PluginCategory;

class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const ATTRIBUTE_CODE = 'category_ids';
    public const STORE_CODE = 'store_id';
    public const CHILDREN_CATEGORIES_SETTING_PATH = 'amshopby/children_categories/';
    public const DEFAULT_CATEGORY_FILTER_IMAGE_SIZE = 20;
    public const MIN_CATEGORY_DEPTH = 1;
    public const CATEGORY_FILTER_PARAM = 'cat';

    /**
     * @var FilterSettingInterface|null
     */
    private ?FilterSettingInterface $setting = null;

    /**
     * @var Manager
     */
    private Manager $categoryManager;

    /**
     * @var Layer|null
     */
    private ?Layer $layer = null;

    /**
     * @var Resolver
     */
    private Resolver $layerResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var CategoryModel|null
     */
    private ?CategoryModel $startCategory = null;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $categoryImageById;

    /**
     * @var ImageModel
     */
    private ImageModel $image;

    /**
     * @var FilterSetting
     */
    private FilterSetting $settingHelper;

    /**
     * @var IsMultiselect
     */
    private IsMultiselect $isMultiselect;

    public function __construct(
        Context $context,
        FilterSetting $settingHelper,
        Manager $categoryManager,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $categoryCollectionFactory,
        ImageModel $image,
        IsMultiselect $isMultiselect
    ) {
        parent::__construct($context);
        $this->settingHelper = $settingHelper;
        $this->categoryManager = $categoryManager;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->image = $image;
        $this->isMultiselect = $isMultiselect;
    }

    /**
     * @return mixed
     */
    public function getStartCategory()
    {
        if ($this->startCategory === null) {
            $this->init();
        }

        return $this->startCategory;
    }

    /**
     * @return bool
     */
    public function isCategoryFilterExtended()
    {
        return $this->getSetting()->getCategoryTreeDepth() > 1;
    }

    /**
     * Category filter initialization
     *
     * @return $this
     */
    private function init()
    {
        if ($this->getSetting()->getCategoryTreeDepth() ==  self::MIN_CATEGORY_DEPTH
            && !$this->getSetting()->getRenderAllCategoriesTree()
            && $this->getLayer()->getCurrentCategory()->getChildrenCount()
        ) {
            $category = $this->getLayer()->getCurrentCategory();
        } elseif ($this->getSetting()->getRenderCategoriesLevel() == RenderCategoriesLevel::ROOT_CATEGORY
            || !!$this->getSetting()->getRenderAllCategoriesTree()
            || $this->getSetting()->getCategoryTreeDepth() ==  self::MIN_CATEGORY_DEPTH
        ) {
            $category = $this->categoryRepository->get(
                $this->categoryManager->getRootCategoryId(),
                $this->categoryManager->getCurrentStoreId()
            );
        } elseif ($this->getSetting()->getRenderCategoriesLevel() == RenderCategoriesLevel::CURRENT_CATEGORY_LEVEL) {
            if ($this->getLayer()->getCurrentCategory()->getId() == $this->categoryManager->getRootCategoryId()) {
                $category = $this->getLayer()->getCurrentCategory();
            } else {
                $categoryId = $this->getLayer()->getCurrentCategory()->getParentId();
                $category = $this->categoryRepository->get($categoryId, $this->categoryManager->getCurrentStoreId());
            }
        } else { //  RenderCategoriesLevel::CURRENT_CATEGORY_CHILDREN
            $category = $this->getLayer()->getCurrentCategory();
        }
        $this->startCategory = $category;

        return $this;
    }

    /**
     * @param $categoryId
     * @param string $imageType
     * @return string|null
     */
    private function getCategoryImage($categoryId, $imageType = 'thumbnail')
    {
        if (empty($this->categoryImageById[$imageType])) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect($imageType);
            foreach ($collection as $item) {
                $this->categoryImageById[$imageType][$item->getId()] = $item->getData($imageType);
            }
        }
        return isset($this->categoryImageById[$imageType][$categoryId])
            ? $this->categoryImageById[$imageType][$categoryId] : null;
    }

    /**
     * @param int $categoryId
     * @param string $imageType
     * @return string
     */
    public function getCategoryImageUrl($categoryId, $imageType = 'thumbnail')
    {
        return $this->getImageUrl(
            $this->getCategoryImage($categoryId, $imageType),
            true,
            $this->getCategoryFilterImageSize()
        );
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    public function isCategoryImageExist($categoryId)
    {
        return (bool)$this->getCategoryImage($categoryId, PluginCategory::THUMBNAIL);
    }

    /**
     * @param $imageName
     * @param bool $withPlaceholder
     * @param null $width
     * @param null $height
     * @return bool|null|string
     */
    public function getImageUrl($imageName, $withPlaceholder = false, $width = null, $height = null)
    {
        return $this->image->getImageUrl($imageName, $withPlaceholder, $width, $height);
    }

    /**
     * @return int
     */
    public function getCategoryFilterImageSize()
    {
        return self::DEFAULT_CATEGORY_FILTER_IMAGE_SIZE;
    }

    /**
     * @param string $path
     * @param bool $flag
     * @return bool|mixed
     */
    private function getChildrenCategoriesSetting($path, $flag = false)
    {
        if ($flag) {
            return $this->scopeConfig->isSetFlag(
                self::CHILDREN_CATEGORIES_SETTING_PATH . $path,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->scopeConfig->getValue(
            self::CHILDREN_CATEGORIES_SETTING_PATH . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getChildrenCategoriesBlockDisplayMode()
    {
        return $this->getChildrenCategoriesSetting('display_mode');
    }

    /**
     * @return string
     */
    public function getAllowCategories()
    {
        return $this->getChildrenCategoriesSetting('categories');
    }

    /**
     * @return bool
     */
    public function isChildrenCategoriesSliderEnabled()
    {
        return $this->getChildrenCategoriesSetting('slider_enabled', true);
    }

    /**
     * @return int
     */
    public function getChildrenCategoriesBlockImageSize()
    {
        return $this->getChildrenCategoriesSetting('image_size');
    }

    /**
     * @return int
     */
    public function getChildrenCategoriesItemsCountPerSlide()
    {
        return $this->getChildrenCategoriesSetting('items_per_slide');
    }

    /**
     * @return bool
     */
    public function showChildrenCategoriesImageLabels()
    {
        return $this->getChildrenCategoriesSetting('show_labels', true);
    }

    /**
     * @return FilterSettingInterface
     */
    public function getSetting()
    {
        if ($this->setting === null) {
            $this->setting = $this->settingHelper->getFilterSettingByCode(self::ATTRIBUTE_CODE);
        }

        return $this->setting;
    }

    /**
     * @return Layer
     */
    public function getLayer()
    {
        if (!$this->layer) {
            $this->layer = $this->layerResolver->get();
        }
        return $this->layer;
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        $filterSetting = $this->getSetting();

        return $this->isMultiselect->execute(
            $filterSetting->getAttributeCode(),
            $filterSetting->isMultiselect(),
            $filterSetting->getDisplayMode()
        );
    }
}
