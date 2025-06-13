<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;

class ConvertProductIdToSku
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $productId): string
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('catalog_product_entity'),
            ['sku']
        )->where('entity_id = ?', $productId);

        return (string)$this->resourceConnection->getConnection()->fetchOne($select);
    }
}
