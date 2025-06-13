<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data\Product;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Amasty\ElasticSearch\Model\Indexer\Data\Product\PriceDataMapper\PriceResolver;
use DateTime;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Store\Model\StoreManager;

/**
 * Map minimum product price in store with considering customer group.
 *
 * Indexed price data can be used in product sorting by price.
 */
class ProductPriceDataMapper implements DataMapperInterface
{
    public const DEFAULT_PRECISION = 4;

    /**
     * @var null|int[]|string[]
     */
    private $customerGroupIds = null;

    /**
     * @var StdlibDateTime
     */
    protected $date;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var PriceDataMapper\PriceResolver
     */
    private $priceResolver;

    public function __construct(
        ResourceConnection $resourceConnection,
        CollectionFactory $customerGroupCollectionFactory,
        StoreManager $storeManager,
        StdlibDateTime $date,
        PriceResolver $priceResolver
    ) {
        $this->resource = $resourceConnection;
        $this->groupCollectionFactory = $customerGroupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->priceResolver = $priceResolver;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $productIds = array_keys($documentData);
        $prices = $this->getProductPriceData($productIds, (int)$storeId);
        $priceDocumentData = [];
        foreach ($productIds as $productId) {
            foreach ($this->getCustomerGroupIds() as $customerGroupId) {
                $priceValue = $prices[$productId][$customerGroupId] ?? 0;
                $priceDocumentData[$productId]['price_' . $customerGroupId] = $priceValue;
            }
        }

        return $priceDocumentData;
    }

    private function getCustomerGroupIds(): array
    {
        if ($this->customerGroupIds === null) {
            $this->customerGroupIds = $this->groupCollectionFactory->create()->getAllIds();
        }

        return $this->customerGroupIds;
    }

    private function getProductPriceData(array $productIds = [], ?int $storeId = null): array
    {
        $result = [];
        if (!empty($productIds)) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $result = $this->priceResolver->getProductPriceData($productIds, $this->getCustomerGroupIds(), $websiteId);

            $connection = $this->resource->getConnection();

            $select = $connection->select()->from(
                $this->resource->getTableName('catalogrule_product_price'),
                ['product_id', 'customer_group_id', 'rule_price']
            );
            $now = new DateTime();
            $select->where('website_id = ?', $websiteId)
                ->where('rule_date = ?', $this->date->formatDate($now, false));
            $select->where('product_id IN (?)', $productIds);
            foreach ($connection->fetchAll($select) as $row) {
                $rulePrice = round($row['rule_price'], self::DEFAULT_PRECISION);
                $productId = $row['product_id'];
                $customerGroupId = $row['customer_group_id'];
                if (isset($result[$productId][$customerGroupId])
                    && $result[$productId][$customerGroupId] > $rulePrice
                ) {
                    $result[$productId][$customerGroupId] = $rulePrice;
                }
            }
        }

        return $result;
    }
}
