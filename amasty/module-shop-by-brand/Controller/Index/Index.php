<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Controller\Index;

use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Amasty\ShopbyBrand\Helper\Data as BrandHelper;
use Magento\Catalog\Model\Design as CatalogDesign;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Block\Html\Breadcrumbs;

class Index implements HttpGetActionInterface
{
    /**
     * @var CatalogSession
     */
    private CatalogSession $catalogSession;

    /**
     * @var CatalogDesign
     */
    private CatalogDesign $catalogDesign;

    /**
     * @var CategoryUrlPathGenerator
     */
    private CategoryUrlPathGenerator $categoryUrlPathGenerator;

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    private ForwardFactory $resultForwardFactory;

    /**
     * @var  CategoryManager
     */
    private CategoryManager $categoryManager;

    /**
     * @var BrandHelper
     */
    private BrandHelper $brandHelper;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var EventManager
     */
    private EventManager $eventManager;

    public function __construct(
        CatalogDesign $catalogDesign,
        CatalogSession $catalogSession,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CategoryManager $categoryManager,
        BrandHelper $brandHelper,
        RequestInterface $request,
        EventManager $eventManager
    ) {
        $this->catalogDesign = $catalogDesign;
        $this->catalogSession = $catalogSession;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->categoryManager = $categoryManager;
        $this->brandHelper = $brandHelper;
        $this->request = $request;
        $this->eventManager = $eventManager;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\CategoryInterface
     */
    private function initCategory()
    {
        return $this->categoryManager->init($this);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->initBrand();
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->initCategory();
        if (!$category) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $settings = $this->catalogDesign->getDesignSettings($category);
        $this->eventManager->dispatch('amshopby_brand_page_init_design', ['design_settings' => $settings]);

        // apply custom design
        if ($settings->getCustomDesign()) {
            $this->catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }

        $this->catalogSession->setLastViewedCategoryId($category->getId());

        $page = $this->resultPageFactory->create();
        // apply custom layout (page) template once the blocks are generated
        if ($settings->getPageLayout()) {
            $page->getConfig()->setPageLayout($settings->getPageLayout());
        }

        $type = 'layered';
        if (!$category->hasChildren()) {
            // Two levels removed from parent.  Need to add default page type.
            $page->addPageLayoutHandles(['type' => $type]);
            $type = 'layered_without_children';
        }

        $page->addPageLayoutHandles(['type' => $type, 'id' => $category->getId()], 'catalog_category_view');

        // apply custom layout update once layout is loaded
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates && is_array($layoutUpdates)) {
            foreach ($layoutUpdates as $layoutUpdate) {
                $page->addUpdate($layoutUpdate);
            }
        }

        $page->getConfig()->addBodyClass('page-products')
            ->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($category))
            ->addBodyClass('category-' . $category->getUrlKey());

        if ($category->getMetaTitle()) {
            $page->getConfig()->getTitle()->set($category->getMetaTitle());
        } else {
            $page->getConfig()->getTitle()->set($category->getName());
        }

        /** @var Breadcrumbs $breadcrumbsBlock */
        $breadcrumbsBlock = $page->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbsBlock) {
            $brandsTitle = $this->brandHelper->getModuleConfig('general/menu_item_label') ?: __('Brands');
            $breadcrumbsBlock->addCrumb(
                'brands',
                [
                    'label' => $brandsTitle,
                    'title' => $brandsTitle,
                    'link' => $this->brandHelper->getAllBrandsUrl()
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'all-products',
                [
                    'label' => $category->getName(),
                    'title' => $category->getName(),
                ]
            );
        }

        return $page;
    }

    /**
     * @return $this
     */
    private function initBrand()
    {
        if ($id = $this->request->getParam('id', false)) {
            $this->request->setParams([$this->brandHelper->getBrandAttributeCode() => $id]);
        }
        return $this;
    }
}
