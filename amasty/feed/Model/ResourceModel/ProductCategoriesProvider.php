<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\ResourceModel;

use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

class ProductCategoriesProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var EntityMetadataInterface
     */
    protected $categoryLinkMetadata;

    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function getCategoryIds(array $productIds = []): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select();
        $select->from(
            $this->getCategoryLinkMetadata()->getEntityTable(),
            ['product_id', new \Zend_Db_Expr('GROUP_CONCAT(category_id)')]
        );

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }
        $select->group('product_id');

        return (array)$connection->fetchPairs($select);
    }

    private function getCategoryLinkMetadata(): EntityMetadataInterface
    {
        if ($this->categoryLinkMetadata === null) {
            $this->categoryLinkMetadata = $this->metadataPool->getMetadata(CategoryLinkInterface::class);
        }

        return $this->categoryLinkMetadata;
    }
}
