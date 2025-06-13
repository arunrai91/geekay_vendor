<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Util;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class ProductIdentifierResolver
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $productId): ?int
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        if ($metadata->getLinkField() == $metadata->getIdentifierField()) {
            return $productId;
        }

        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $select = $connection->select()
            ->from($metadata->getEntityTable(), [$metadata->getIdentifierField()])
            ->where($metadata->getLinkField() . ' = ?', $productId);
        $productId = $connection->fetchOne($select);

        return $productId ? (int)$productId : null;
    }
}
