<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\ValidProductSnapshot;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct;
use Amasty\Feed\Model\ValidProductSnapshot\ResourceModel\ValidProductSnapshot;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class SnapshotTransferService
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ResourceConnection $connection,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function migrateProducts(array $feedIds): void
    {
        if (empty($feedIds)) {
            return;
        }

        $connection = $this->connection->getConnection();
        $connection->beginTransaction();
        try {
            $connection->delete(
                $this->connection->getTableName(ValidProductSnapshot::TABLE_NAME),
                [ValidProductsInterface::FEED_ID . ' IN (?)' => $feedIds]
            );
            $this->migrate($feedIds);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error($e->getMessage());
        }
    }

    private function migrate(array $feedIds): void
    {
        $connection = $this->connection->getConnection();
        $productTable = $this->connection->getTableName(ValidProduct::TABLE_NAME);
        $productSnapshotTable = $this->connection->getTableName(ValidProductSnapshot::TABLE_NAME);

        $select = $connection->select(
        )->from(
            ['pt' => $productTable],
            [ValidProductsInterface::FEED_ID, ValidProductsInterface::VALID_PRODUCT_ID]
        )->where('pt.' . ValidProductsInterface::FEED_ID . ' IN (?)', $feedIds);

        $connection->query(
            $connection->insertFromSelect(
                $select,
                $productSnapshotTable,
                [ValidProductsInterface::FEED_ID, ValidProductsInterface::VALID_PRODUCT_ID]
            )
        );
    }
}
