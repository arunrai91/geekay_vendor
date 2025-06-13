<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Model\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Manager extends \Magento\Framework\DataObject
{
    public const CATEGORY_FORCE_MIXED_MODE = 'amshopby_force_mixed_mode';

    public const CATEGORY_SHOPBY_IMAGE_URL = 'amshopby_category_image_url';

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var CatalogSession
     */
    private CatalogSession $catalogSession;

    /**
     * @var EventManager
     */
    private EventManager $eventManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        CatalogSession $catalogSession,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->eventManager = $context->getEventManager();
        $this->logger = $logger;
        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function getRootCategoryId()
    {
        return $this->storeManager->getStore()->getRootCategoryId();
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    public function getCurrentStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * @param null $controllerAction
     * @return bool|\Magento\Catalog\Api\Data\CategoryInterface
     */
    public function init($controllerAction = null)
    {
        $category = $this->getCategoryModel();
        if ($category) {
            $this->catalogSession->setLastVisitedCategoryId($category->getId());
            if (!$this->coreRegistry->registry('current_category')) {
                $this->coreRegistry->register('current_category', $category);
            }

            $this->eventManager->dispatch(
                'amshopby_category_manager_init_after',
                ['category' => $category]
            );

            try {
                $this->eventManager->dispatch(
                    'catalog_controller_category_init_after',
                    ['category' => $category, 'controller_action' => $controllerAction]
                );
            } catch (LocalizedException $e) {
                $this->logger->critical($e);
                return false;
            }
        }

        return $category;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\CategoryInterface
     */
    private function getCategoryModel()
    {
        $categoryId = $this->getRootCategoryId();
        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->getCurrentStoreId());
            // Workaround to show filters on 'all-products' in Magento 2.2
            $category->setData('is_anchor', 1);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        return $category;
    }
}
