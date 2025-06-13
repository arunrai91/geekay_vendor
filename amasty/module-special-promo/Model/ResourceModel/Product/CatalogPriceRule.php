<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Base for Magento 2
 */

namespace Amasty\Rules\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;

class CatalogPriceRule
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @deprecated Use getCatalogRuleForProducts instead to get Catalog Rules for all products in quote
     *
     * @return bool|string
     */
    public function getCatalogRuleProduct(int $productId, int $websiteId, int $customerGroupId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalogrule_product');
        $select = $connection->select()->from($tableName, 'rule_product_id')
            ->where('product_id = ?', $productId)
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('from_time = 0 or from_time < NOW()')
            ->where('to_time = 0 or to_time > NOW()')
            ->limit(1);

        return $connection->fetchOne($select);
    }

    public function getCatalogRuleForProducts(array $productIds, int $websiteId, int $customerGroupId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalogrule_product');
        $select = $connection->select()->from($tableName, 'product_id')
            ->where('product_id IN (?)', $productIds)
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('from_time = 0 or from_time < ?', time())
            ->where('to_time = 0 or to_time > ?', time());

        return $connection->fetchCol($select);
    }
}
