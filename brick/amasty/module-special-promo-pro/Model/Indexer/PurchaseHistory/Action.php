<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Indexer\PurchaseHistory;

use Amasty\RulesPro\Model\ResourceModel\Indexer\Order;
use Psr\Log\LoggerInterface;

class Action
{
    /**
     * @var Order
     */
    private $orderResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Order $orderResource,
        LoggerInterface $logger
    ) {
        $this->orderResource = $orderResource;
        $this->logger = $logger;
    }

    public function convertOrderIdsToCustomerIds(array $orderIds): array
    {
        return $this->orderResource->retrieveCustomerIdsByOrderIds($orderIds);
    }

    /**
     * @param array $ids customer ids
     *
     * @return \Generator
     */
    public function getIndexInsertIterator(array $ids = []): \Generator
    {
        try {
            foreach ($this->orderResource->retrieveIndexData($ids) as $data) {
                if ($index = $this->formatIndexData($data)) {
                    yield $data['customer_id'] => $index;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    private function formatIndexData(array $data): array
    {
        if (empty($data['customer_id'])) {
            return [];
        }

        return [
            IndexStructure::CUSTOMER_ID => (int)$data['customer_id'],
            IndexStructure::SUM_AMOUNT => $data['s'] ?? .0,
            IndexStructure::ORDERS_COUNT => $data['c'] ?? 0
        ];
    }
}
