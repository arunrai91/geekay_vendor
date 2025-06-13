<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Indexer\Data\Product\PriceDataMapper;

use Amasty\ElasticSearch\Model\Indexer\Data\Product\ProductPriceDataMapper;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

class PriceResolver
{
    /**
     * @var IndexScopeResolverInterface
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var DimensionModeConfiguration
     */
    private $dimensionModeConfiguration;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        IndexScopeResolverInterface $priceTableResolver,
        DimensionFactory $dimensionFactory,
        DimensionModeConfiguration $dimensionModeConfiguration,
        ResourceConnection $resourceConnection
    ) {
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
        $this->resource = $resourceConnection;
    }

    public function getProductPriceData(array $productIds, array $customerGroupIds, int $websiteId): array
    {
        $priceTableNames = $this->resolvePriceTableNames($websiteId, $customerGroupIds);
        $result = [];
        $connection = $this->resource->getConnection();

        foreach ($priceTableNames as $tableName) {
            $select = $connection->select()->from(
                $this->resource->getTableName($tableName),
                ['entity_id', 'customer_group_id', 'min_price']
            );

            $select->where('website_id = ?', $websiteId);
            $select->where('entity_id IN (?)', $productIds);

            foreach ($connection->fetchAll($select) as $row) {
                $result[$row['entity_id']][$row['customer_group_id']] = round(
                    (float)$row['min_price'],
                    ProductPriceDataMapper::DEFAULT_PRECISION
                );
            }
        }

        return $result;
    }

    /**
     * Collect price index table names
     *
     * Price index can have dimensions for customer group and website
     *
     * @param int $websiteId
     * @param int[]|string[] $customerGroupIds
     * @return string[] DB table names
     */
    private function resolvePriceTableNames(int $websiteId, array $customerGroupIds): array
    {
        $dimensionConfiguration = $this->dimensionModeConfiguration->getDimensionConfiguration();
        $isCustomerGroup = false;
        $websiteDimension = null;
        foreach ($dimensionConfiguration as $dimensionName) {
            if ($dimensionName === WebsiteDimensionProvider::DIMENSION_NAME) {
                $websiteDimension = $this->createDimensionFromWebsite($websiteId);
            }
            if ($dimensionName === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $isCustomerGroup = true;
            }
        }

        $priceTableNames = [];

        if ($isCustomerGroup) {
            foreach ($customerGroupIds as $groupId) {
                $dimensions = [];
                if ($websiteDimension) {
                    $dimensions[] = $websiteDimension;
                }
                $dimensions[] = $this->createDimensionFromCustomerGroup((int)$groupId);
                $priceTableNames[] = $this->priceTableResolver->resolve('catalog_product_index_price', $dimensions);
            }
        } elseif ($websiteDimension) {
            $priceTableNames[] = $this->priceTableResolver->resolve('catalog_product_index_price', [$websiteDimension]);
        } else {
            $priceTableNames[] = 'catalog_product_index_price';
        }

        return $priceTableNames;
    }

    private function createDimensionFromWebsite(int $websiteId): Dimension
    {
        return $this->dimensionFactory->create(
            WebsiteDimensionProvider::DIMENSION_NAME,
            (string)$websiteId
        );
    }

    private function createDimensionFromCustomerGroup(int $customerGroupId): Dimension
    {
        return $this->dimensionFactory->create(
            CustomerGroupDimensionProvider::DIMENSION_NAME,
            (string)$customerGroupId
        );
    }
}
