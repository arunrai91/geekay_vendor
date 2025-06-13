<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\ElasticsearchCatalogPermissions\Plugin;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Search\Model\EngineResolver;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\CatalogPermissions\Model\Permission;
use Magento\CatalogPermissions\App\Config;
use Magento\ElasticsearchCatalogPermissions\Plugin\AddCategoryPermissionsToCollection;

class AddCategoryPermissionsToCollectionPlugin
{
    /**
     * Flag to check that category permissions filters already added
     */
    public const PERMISSION_FILTER_ADDED_FLAG = 'permission_filter_added';

    /**
     * @var bool
     */
    private $inited = false;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Customer session instance
     *
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        EngineResolverInterface $engineResolver,
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->engineResolver = $engineResolver;
    }

    /**
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false)
    {
        if (!$productCollection->isLoaded()) {
            $result = $this->execute($productCollection);
        }

        return [$printQuery, $logQuery];
    }

    public function beforeGetFacetedData(
        Collection $productCollection,
        string $field,
        SearchResultInterface $searchResult = null
    ) {
        $this->execute($productCollection);

        return [$field, $searchResult];
    }

    /**
     * Add catalog permissions before get select count
     *
     * @param Collection $productCollection
     * @see Collection::getSelectCountSql
     */
    public function beforeGetSelectCountSql(Collection $productCollection)
    {
        if (!$productCollection->isLoaded()) {
            $this->execute($productCollection);
        }
    }

    /**
     * @param Collection $productCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function execute(Collection $productCollection)
    {
        if ($this->avoidApplyPermissions($productCollection)) {
            return;
        }

        $this->init();
        if (!$this->config->isEnabled()) {
            return;
        }

        $categoryPermissionAttribute = $this->attributeAdapterProvider->getByAttributeCode('category_permission');
        $categoryPermissionKey = $this->fieldNameResolver->getFieldName(
            $categoryPermissionAttribute,
            [
                'storeId' => $this->storeManager->getStore()->getId(),
                'customerGroupId' => $this->customerSession->getCustomerGroupId(),
            ]
        );

        $productCollection->addFieldToFilter('category_permissions_field', $categoryPermissionKey);
        $productCollection->addFieldToFilter(
            'category_permissions_value',
            Permission::PERMISSION_DENY // @phpstan-ignore class.notFound
        );

        $productCollection->setFlag(self::PERMISSION_FILTER_ADDED_FLAG, true);
    }

    private function init()
    {
        if (!$this->inited) {
            $this->customerSession = $this->objectManager->get(Session::class);
            $this->storeManager = $this->objectManager->get(StoreManager::class);
            $this->fieldNameResolver = $this->objectManager->get(ResolverInterface::class);
            $this->attributeAdapterProvider = $this->objectManager->get(AttributeProvider::class);
            $this->config = $this->objectManager->get(Config::class); // @phpstan-ignore class.notFound
            $this->inited = true;
        }
    }

    /**
     * Whether to avoid apply permission to collection
     */
    private function avoidApplyPermissions(Collection $productCollection): bool
    {
        return $productCollection->getFlag(self::PERMISSION_FILTER_ADDED_FLAG)
            || !$this->moduleManager->isEnabled('Magento_ElasticsearchCatalogPermissions')
            || !class_exists(AddCategoryPermissionsToCollection::class)
            || $this->isCurrentEngineMysql();
    }

    /**
     * Check if current engine is MYSQL.
     *
     * @return bool
     */
    private function isCurrentEngineMysql()
    {
        return $this->engineResolver->getCurrentSearchEngine() === EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE;
    }
}
