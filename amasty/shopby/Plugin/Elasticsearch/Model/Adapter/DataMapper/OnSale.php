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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper
     */
    private $onSaleHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $customerGrouprCollectionFactory;

    /**
     * @var array
     */
    private $onSaleProductIds = [];

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
        $collection = $this->customerGrouprCollectionFactory->create();
        $mappedData = [];
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        foreach ($collection as $item) {
            $mappedData[self::FIELD_NAME . '_' . $item->getId() . '_' . $websiteId] =
                    (int)$this->onSaleProvider->isProductOnSale((int) $entityId, (int) $storeId, (int) $item->getId());
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
}
