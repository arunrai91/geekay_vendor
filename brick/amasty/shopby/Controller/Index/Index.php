<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Controller\Index;

use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Magento\Catalog\Model\Design as CatalogDesign;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Block\Html\Breadcrumbs;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

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

    public function __construct(
        Context $context,
        CatalogDesign $catalogDesign,
        CatalogSession $catalogSession,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        CategoryManager $categoryManager
    ) {
        $this->catalogDesign = $catalogDesign;
        $this->catalogSession = $catalogSession;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->categoryManager = $categoryManager;
        $this->response = $context->getResponse();
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\CategoryInterface
     */
    private function initCategory()
    {
        return $this->categoryManager->init($this);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->initCategory();
        if (!$category) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $settings = $this->catalogDesign->getDesignSettings($category);

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
        $type = $category->hasChildren() ? 'layered' : 'layered_without_children';

        if (!$category->hasChildren()) {
            // Two levels removed from parent.  Need to add default page type.
            $parentType = strtok($type, '_');
            $page->addPageLayoutHandles(['type' => $parentType]);
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
     * Retrieve response object
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
