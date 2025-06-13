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
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class GetValidFeedProducts
{
    public const BATCH_SIZE = 1000;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var RuleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * @var Builder
     */
    protected Builder $sqlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Product
     */
    private Product $productResource;

    /**
     * @var FilterProvider
     */
    private FilterProvider $filterProvider;

    public function __construct(
        RuleFactory $ruleFactory,
        CollectionFactory $productCollectionFactory,
        Builder $sqlBuilder,
        ?InventoryResolver $inventoryResolver, // @deprecated
        StoreManagerInterface $storeManager,
        Product $productResource,
        ?FilterProvider $filterProvider = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;
        $this->filterProvider = $filterProvider ?? ObjectManager::getInstance()->get(FilterProvider::class);
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
        foreach ($this->filterProvider->getFilters() as $filter) {
            $filter->apply($productCollection, $model);
        }

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
}
