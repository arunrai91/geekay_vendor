<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Model\Ajax;

use Amasty\Shopby\Block\Product\ProductList\Ajax;
use Amasty\Shopby\Helper\State;
use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Block\Category\View as CategoryView;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Category;
use Magento\CatalogSearch\Block\Result as SearchResult;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\PageCache\Model\Cache\Type;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxResponseBuilder
{
    public const OSN_CONFIG = 'amasty.xnotif.config';

    public const QUICKVIEW_CONFIG = 'amasty.quickview.config';

    public const SORTING_CONFIG = 'amasty.sorting.direction';

    public const ILN_FILTER_ANALYTICS = 'amasty.shopby.filter_analytics';
    public const BANNERS_CATEGORY_ADD_TO_CART = 'ambanners.category.add.to.cart';

    /**
     * @var string[]
     */
    private $tags = [];

    /**
     * @var State
     */
    private $stateHelper;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Config
     */
    private $pageConfig;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var UrlAjaxParams
     */
    private $ajaxParams;

    /**
     * @var array{
     *     'themeName': array{
     *         'image': string,
     *         'description': string,
     *     }
     * }
     */
    private $layoutMapping;

    public function __construct(
        State $stateHelper,
        DesignInterface $design,
        LayoutInterface $layout,
        Config $pageConfig,
        DataObjectFactory $dataObjectFactory,
        ManagerInterface $eventManager,
        UrlAjaxParams $ajaxParams,
        array $layoutMapping = []
    ) {
        $this->stateHelper = $stateHelper;
        $this->design = $design;
        $this->layout = $layout;
        $this->pageConfig = $pageConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
        $this->ajaxParams = $ajaxParams;
        $this->layoutMapping = $layoutMapping;
    }

    /**
     * Resolve associative array for AJAX response on catalog pages.
     *
     * Result array will be processed by JS on frontend
     *
     * @return array{
     *            'categoryProducts': string,
     *            'navigation': string,
     *            'navigationTop': string,
     *            'breadcrumbs': string,
     *            'h1': string,
     *            'title': string,
     *            'bottomCmsBlock': string,
     *            'url': string,
     *            'tags': string,
     *            'js_init': string,
     *            'isDisplayModePage': bool,
     *            'currentCategoryId': string,
     *            'currency': string,
     *            'store': string,
     *            'store_switcher': string,
     *            'behaviour': string
     *           }
     */
    public function build(): array
    {
        $products = $this->resolveProductsBlock();
        $productList = $this->resolveProductList($products);
        $categoryProducts = '';
        if ($products) {
            $categoryProducts = $this->applyEventChanges($products->toHtml());
            $this->addXTagCache($products);
        }

        $navigationHtml = $this->getCachableBlockHtml('catalog.leftnav');
        if (!$navigationHtml) {
            $navigationHtml = $this->getCachableBlockHtml('catalogsearch.leftnav');
        }

        $navigationTopHtml = '';
        if (strpos($categoryProducts, 'amasty-catalog-topnav') === false) {
            $navigationTopHtml = $this->getCachableBlockHtml('amshopby.catalog.topnav');
        }

        $swatchesChooseHtml = $this->getBlockHtml('catalog.navigation.swatches.choose');
        $currentCategory = $this->resolveCurrentCategory($productList);
        $isDisplayModePage = $currentCategory && $currentCategory->getDisplayMode() === Category::DM_PAGE;
        $responseData = [
            'categoryProducts' => $categoryProducts . $swatchesChooseHtml . $this->getAdditionalConfigs(),
            'navigation' =>
                $navigationHtml
                . $this->getCachableBlockHtml('catalog.navigation.collapsing')
                . $this->getCachableBlockHtml('amasty.shopby.applybutton.sidebar'),
            'navigationTop' =>
                $navigationTopHtml
                . $this->getCachableBlockHtml('amasty.shopby.applybutton.topnav'),
            'breadcrumbs' => $this->getCachableBlockHtml('breadcrumbs'),
            'h1' => $this->getCachableBlockHtml('page.main.title'),
            'title' => $this->pageConfig->getTitle()->get(),
            'bottomCmsBlock' => $this->getBlockHtml('amshopby.bottom'),
            'url' => $this->stateHelper->getCurrentUrl(),
            'tags' => $this->getTagsString(),
            'js_init' => $this->getCachableBlockHtml('amasty.shopby.jsinit'),
            'isDisplayModePage' => $isDisplayModePage,
            'currentCategoryId' => $this->resolveCategoryId($currentCategory),
            'currency' => $this->getBlockHtml('currency'),
            'store' => $this->getBlockHtml('store_language'),
            'store_switcher' => $this->getBlockHtml('store_switcher'),
            'behaviour' => $this->getBlockHtml('wishlist_behaviour')
        ];

        $responseData['productsCount'] = ($productList
            ? $productList->getLoadedProductCollection()->getSize()
            : $products->getResultCount());

        $this->addClearUrl($responseData);
        $this->addCategoryData($responseData);
        $this->addSidebarAdditional($responseData);
        $this->applyEventChangesForResponse($responseData);

        $responseData = $this->ajaxParams->removeAjaxParam($responseData);
        $responseData = $this->ajaxParams->removeEncodedAjaxParams($responseData);

        $this->resetTags();

        return $responseData;
    }

    /**
     * @return CategoryView|SearchResult|null
     */
    private function resolveProductsBlock(): ?Template
    {
        $products = $this->layout->getBlock('category.products');
        if (!$products) {
            $products = $this->layout->getBlock('search.result');
        }

        if (!$products) {
            return null;
        }

        return $products;
    }

    private function resolveProductList($products): ?ListProduct
    {
        if (!$products) {
            return null;
        }
        $productList = $products->getChildBlock('product_list');
        if (!$productList) {
            $productList = $products->getChildBlock('search_result_list');
        }

        if (!$productList) {
            return null;
        }

        return $productList;
    }

    private function applyEventChanges(string $html)
    {
        $dataObject = $this->dataObjectFactory->create(
            [
                'data' => [
                    'page' => $html,
                    'pageType' => 'catalog_category_view'
                ]
            ]
        );
        $this->eventManager->dispatch('amoptimizer_process_ajax_page', ['data' => $dataObject]);

        return $dataObject->getData('page');
    }

    /**
     * @param null|false|Template $element
     */
    public function addXTagCache($element): void
    {
        if ($element instanceof IdentityInterface) {
            foreach ($element->getIdentities() as $item) {
                $this->tags[] = $item;
            }
        }
    }

    private function getCachableBlockHtml(string $blockName): string
    {
        $block = $this->layout->getBlock($blockName);
        if (!$block) {
            return '';
        }
        $html = $block->toHtml();
        $this->addXTagCache($block);

        return $html;
    }

    /**
     * @param string $blockName
     * @return string
     */
    private function getBlockHtml(string $blockName): string
    {
        $block = $this->layout->getBlock($blockName);

        return $block ? $block->toHtml() : '';
    }

    /**
     * @return Category|null
     */
    private function resolveCurrentCategory(?ListProduct $productList): ?CategoryInterface
    {
        if ($productList && $productList->getLayer()) {
            return $productList->getLayer()->getCurrentCategory();
        }

        return null;
    }

    /**
     * @return string
     */
    private function getAdditionalConfigs(): string
    {
        $html = $this->getBlockHtml(self::OSN_CONFIG);
        $html .= $this->getBlockHtml(self::QUICKVIEW_CONFIG);
        $html .= $this->getBlockHtml(self::SORTING_CONFIG);
        $html .= $this->getBlockHtml(self::ILN_FILTER_ANALYTICS);
        $html .= $this->getBlockHtml(self::BANNERS_CATEGORY_ADD_TO_CART);

        return $html;
    }

    public function getTagsString(): string
    {
        return implode(',', array_unique($this->tags));
    }

    private function resolveCategoryId($currentCategory): int
    {
        if ($currentCategory && $currentCategory->getId()) {
            return (int)$currentCategory->getId();
        }

        return 0;
    }

    private function addClearUrl(array &$responseData): void
    {
        if ($ajax = $this->layout->getBlock('category.amshopby.ajax')) {
            /** @var Ajax $ajax */
            $responseData['newClearUrl'] = $ajax->getClearUrl();
        }
    }

    private function addCategoryData(array &$responseData): void
    {
        $themeCode = $this->design->getDesignTheme()->getCode();
        if (array_key_exists($themeCode, $this->layoutMapping)) {
            $responseData['image'] = $this->getBlockHtml(
                $this->layoutMapping[$themeCode]['image']
            );
            $responseData['description'] = $this->getBlockHtml(
                $this->layoutMapping[$themeCode]['description']
            );
        } else {
            $responseData['categoryData'] = '<div class="category-view">' . $this->resolveHtmlCategoryData() . '</div>';
            $responseData['description'] = $this->layout->getBlock('category.description')
                ? $this->layout->renderElement('category.description')
                : '';
        }
    }

    private function resolveHtmlCategoryData(): string
    {
        $htmlCategoryData = '';
        $children = $this->layout->getChildNames('category.view.container');
        foreach ($children as $child) {
            $htmlCategoryData .= $this->layout->renderElement($child);
            $this->addXTagCache($child);
        }

        return $htmlCategoryData;
    }

    private function addSidebarAdditional(array &$responseData): void
    {
        try {
            $sidebarTag = $this->layout->getElementProperty('div.sidebar.additional', Element::CONTAINER_OPT_HTML_TAG);
            $sidebarClass =
                $this->layout->getElementProperty('div.sidebar.additional', Element::CONTAINER_OPT_HTML_CLASS);
            $sidebarAdditional = $this->layout->renderNonCachedElement('div.sidebar.additional');
            $responseData['sidebar_additional'] = $sidebarAdditional;
            $responseData['sidebar_additional_alias'] = $sidebarTag . '.' . str_replace(' ', '.', $sidebarClass);
        } catch (Exception $e) {
            unset($responseData['sidebar_additional']);
        }
    }

    public function resetTags(): void
    {
        $this->tags = [Type::CACHE_TAG];
    }

    private function applyEventChangesForResponse(array &$responseData): void
    {
        $dataObject = $this->dataObjectFactory->create(
            [
                'data' => [
                    'responseData' => $responseData,
                ]
            ]
        );
        $this->eventManager->dispatch('amfinder_process_ajax_page', ['data' => $dataObject]);
        $responseData = $dataObject->getData('responseData');
    }
}
