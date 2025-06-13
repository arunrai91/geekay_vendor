<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

class OnSale implements DataMapperInterface
{
    public const FIELD_NAME = 'am_on_sale';

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $customerGrouprCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Shopby\Model\Search\DataProvider\Product\OnSaleProvider
     */
    private $onSaleProvider;

    /**
     * @var int[]|null
     */
    private $customerGroupIds;

    /**
     * @var array
     */
    private $onSaleProductIds = [];

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Shopby\Model\Search\DataProvider\Product\OnSaleProvider $onSaleProvider
    ) {
        $this->customerGrouprCollectionFactory = $customerGroupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->onSaleProvider = $onSaleProvider;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []): array
    {
        $mappedData = [];
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        foreach ($this->getCustomerGroupIds() as $customerGroupId) {
            $mappedData[self::FIELD_NAME . '_' . $customerGroupId . '_' . $websiteId] = (int)in_array(
                $entityId,
                $this->onSaleProductIds[$storeId][$customerGroupId] ?? []
            );
        }
        return $mappedData;
    }

    public function isAllowed(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amshopby/am_on_sale_filter/enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFieldName(): string
    {
        return self::FIELD_NAME;
    }

    /**
     * @param int $storeId
     * @param int[] $productIds
     */
    public function preloadCacheData(int $storeId, array $productIds): void
    {
        foreach ($this->getCustomerGroupIds() as $customerGroupId) {
            $this->onSaleProductIds[$storeId][$customerGroupId] = $this->onSaleProvider->loadOnSaleProductIds(
                $storeId,
                $customerGroupId,
                $productIds
            );
        }
    }

    public function clearCacheData(): void
    {
        $this->onSaleProductIds = [];
    }

    /**
     * @return int[]
     */
    private function getCustomerGroupIds(): array
    {
        if ($this->customerGroupIds === null) {
            $collection = $this->customerGrouprCollectionFactory->create();
            $this->customerGroupIds = array_map(function ($customerGroupId) {
                return (int)$customerGroupId;
            }, $collection->getAllIds());
        }

        return $this->customerGroupIds;
    }
}
