<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Banners Lite for Magento 2 (System)
 */

namespace Amasty\BannersLite\Model;

use Magento\Customer\Model\Context;
use Magento\Framework\Data\Collection;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Rule;

class ProductBannerProvider
{
    /**
     * @var array with product banners
     */
    private $productBanners;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ResourceModel\BannerRule\CollectionFactory
     */
    private $bannerRuleFactory;

    /**
     * @var ResourceModel\Rule\CollectionFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceModel\Banner\CollectionFactory
     */
    private $bannerFactory;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleCartPrice;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\BannersLite\Model\ResourceModel\BannerRule\CollectionFactory $bannerRuleFactory,
        \Amasty\BannersLite\Model\ResourceModel\Rule\CollectionFactory $ruleFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Amasty\BannersLite\Model\ResourceModel\Banner\CollectionFactory $bannerFactory,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleCartPrice,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->productRepository = $productRepository;
        $this->bannerRuleFactory = $bannerRuleFactory;
        $this->ruleFactory = $ruleFactory;
        $this->metadataPool = $metadataPool;
        $this->bannerFactory = $bannerFactory;
        $this->ruleCartPrice = $ruleCartPrice;
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
    }

    /**
     * Get Banners for product
     *
     * @param int $productId
     *
     * @return array
     */
    public function getBanners($productId)
    {
        if (!isset($this->productBanners[$productId])) {
            $this->productBanners[$productId] = [];
            if (!empty($ruleIds = $this->getValidRulesIds($productId))) {
                $this->productBanners[$productId] = $this->bannerFactory->create()->getBySalesruleIds($ruleIds);
            }
        }

        return $this->productBanners[$productId];
    }

    /**
     * @param int $productId
     * @param string[]|null $allowedActions
     * @return array
     * @api
     */
    public function getValidRulesIds(int $productId, ?array $allowedActions = null): array
    {
        $bannerRuleIds = $this->getBannerRuleIds($productId);
        $ruleIds = $this->getActiveRuleIds($bannerRuleIds, $allowedActions);

        return $ruleIds;
    }

    /**
     * @api
     */
    public function getProductPriorityRule(int $productId): ?Rule
    {
        $bannerRuleIds = $this->getBannerRuleIds($productId);
        if (empty($bannerRuleIds)) {
            return null;
        }

        $linkField = $this->metadataPool->getMetadata(RuleInterface::class)->getLinkField();
        $ruleCollection = $this->ruleFactory->create();
        $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $ruleCollection->addWebsiteGroupDateFilter($websiteId, $customerGroupId)
            ->prepareSqlConditions($linkField, $bannerRuleIds);
        $ruleCollection->setOrder('sort_order', Collection::SORT_ORDER_ASC)
            ->setPageSize(1);

        return $ruleCollection->getFirstItem();
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    private function getBannerRuleIds($productId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);
        /** @var \Amasty\BannersLite\Model\ResourceModel\BannerRule\Collection $collection */
        $collection = $this->bannerRuleFactory->create();

        return $collection->getValidBannerRuleIds($product->getSku(), $product->getCategoryIds());
    }

    /**
     * @param array $bannerRuleIds
     * @param string[]|null $allowedActions
     *
     * @return array
     */
    private function getActiveRuleIds(array $bannerRuleIds, ?array $allowedActions = null): array
    {
        if (empty($bannerRuleIds)) {
            return [];
        }
        $linkField = $this->metadataPool->getMetadata(RuleInterface::class)->getLinkField();
        /** @var \Amasty\BannersLite\Model\ResourceModel\Rule\Collection $ruleCollection */
        $ruleCollection = $this->ruleFactory->create();
        $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $ruleCollection->addWebsiteGroupDateFilter($websiteId, $customerGroupId);

        return $ruleCollection->getActiveRuleIds($linkField, $bannerRuleIds, $allowedActions);
    }

    public function _resetState(): void
    {
        $this->productBanners = null;
    }
}
