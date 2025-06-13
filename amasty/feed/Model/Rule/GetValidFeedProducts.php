<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Rule;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\InventoryResolver;
use Amasty\Feed\Model\Rule\Condition\Sql\Builder;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class GetValidFeedProducts
{
    public const BATCH_SIZE = 1000;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Builder
     */
    protected $sqlBuilder;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Product
     */
    private $productResource;

    public function __construct(
        RuleFactory $ruleFactory,
        CollectionFactory $productCollectionFactory,
        Builder $sqlBuilder,
        InventoryResolver $inventoryResolver,
        StoreManagerInterface $storeManager,
        Product $productResource
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->inventoryResolver = $inventoryResolver;
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;
    }

    public function execute(Feed $model, array $ids = []): void
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditionsSerialized($model->getConditionsSerialized());
        $rule->setStoreId($model->getStoreId());
        $this->storeManager->setCurrentStore($model->getStoreId());
        $model->setRule($rule);
        $this->updateIndex($model, $ids);
    }

    public function updateIndex(Feed $model, array $ids = []): void
    {
        $productCollection = $this->prepareCollection($model, $ids);
        $productIdField = 'e.' . $this->productResource->getIdFieldName();
        $productSelect = $this->getProductSelect($productCollection, $productIdField, (int)$model->getEntityId());
        $productSelect->order(sprintf('%s ASC', $productIdField));

        $lastValidProductId = 0;
        $connection = $this->productResource->getConnection();
        while ($lastValidProductId >= 0) {
            $productSelect->where(sprintf('%s > %s', $productIdField, $lastValidProductId));
            $validItemsData = $connection->fetchAll($productSelect);
            if (empty($validItemsData)) {
                break;
            }

            $connection->insertMultiple(
                $this->productResource->getTable(ValidProduct::TABLE_NAME),
                $validItemsData
            );
            $lastValidProduct = array_pop($validItemsData);
            $lastValidProductId = $lastValidProduct[ValidProductsInterface::VALID_PRODUCT_ID] ?? -1;
        }
    }

    private function prepareCollection(Feed $model, array $ids = []): ProductCollection
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($model->getStoreId());

        if (!empty($ids)) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
        }
        $this->addExcludeFilters($productCollection, $model);
        $this->addConditionFilters($productCollection, $model);

        return $productCollection;
    }

    private function getProductSelect(
        ProductCollection $productCollection,
        string $productIdField,
        int $feedId
    ): Select {
        $productSelect = $productCollection->getSelect();
        $productSelect->reset(Select::COLUMNS)
            ->columns(
                [
                    ValidProductsInterface::ENTITY_ID => new \Zend_Db_Expr('null'),
                    ValidProductsInterface::FEED_ID => new \Zend_Db_Expr($feedId),
                    ValidProductsInterface::VALID_PRODUCT_ID => $productIdField
                ]
            );
        //fix for magento 2.3.2 for big number of products
        $productSelect->reset(Select::ORDER)
            ->distinct()
            ->limit(self::BATCH_SIZE);

        return $productSelect;
    }

    private function addExcludeFilters(ProductCollection $productCollection, Feed $model): void
    {
        $excludedIds = [];
        if ($model->getExcludeDisabled()) {
            $productCollection->addAttributeToFilter(
                'status',
                ['eq' => Status::STATUS_ENABLED]
            );
            if ($model->getExcludeSubDisabled()) {
                $excludedIds = $this->getSubDisabledIds((int)$model->getStoreId());
            }
        }

        if ($model->getExcludeNotVisible()) {
            $productCollection->addAttributeToFilter(
                'visibility',
                ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]
            );
        }

        if ($model->getExcludeOutOfStock()) {
            $outOfStockProductIds = $this->inventoryResolver->getOutOfStockProductIds();
            $excludedIds = array_unique(array_merge($excludedIds, $outOfStockProductIds));
        }

        if (!empty($excludedIds)) {
            $productCollection->addFieldToFilter(
                'entity_id',
                ['nin' => $excludedIds]
            );
        }
    }

    private function addConditionFilters(ProductCollection $productCollection, Feed $model): void
    {
        $conditions = $model->getRule()->getConditions();
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);
    }

    private function getSubDisabledIds(int $storeId): array
    {
        $disabledParentProductsSelect = $this->getDisabledParentProductsSelect($storeId);

        $subDisabledProductsCollection = $this->productCollectionFactory->create();
        $subDisabledProductsCollection->getSelect()->join(
            ['rel' => $this->productResource->getTable('catalog_product_relation')],
            'e.entity_id = rel.child_id',
            []
        )->where('rel.parent_id IN (?)', $disabledParentProductsSelect);

        return $subDisabledProductsCollection->getAllIds();
    }

    private function getDisabledParentProductsSelect(int $storeId): Select
    {
        $disabledParentsCollection = $this->productCollectionFactory->create();
        $linkField = $disabledParentsCollection->getProductEntityMetadata()->getLinkField();

        $disabledParentsCollection->addStoreFilter($storeId);
        $disabledParentsCollection->addAttributeToFilter(
            'status',
            ['eq' => Status::STATUS_DISABLED]
        );

        return $disabledParentsCollection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(['e.' . $linkField])
            ->join(
                ['rel' => $this->productResource->getTable('catalog_product_relation')],
                'rel.parent_id = e.' . $linkField,
                []
            )->distinct();
    }
}
