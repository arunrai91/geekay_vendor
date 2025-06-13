<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Observer\Sales\Order;

use Amasty\RulesPro\Model\Indexer\PurchaseHistory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Sales\Model\Order;

/**
 * sales_order_save_after
 */
class UpdateIndex implements ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order && $this->isOrderValid($order)) {
            $indexer = $this->indexerRegistry->get(PurchaseHistory::INDEXER_ID);
            if (!$indexer->isScheduled()) {
                $indexer->reindexRow((int)$order->getId());
            }
        }
    }

    private function isOrderValid(Order $order): bool
    {
        return $order->getId()
            && !$order->getCustomerIsGuest()
            && $order->getCustomerId()
            && $this->isOrderStateValid($order);
    }

    private function isOrderStateValid(Order $order): bool
    {
        return $order->getState() === Order::STATE_COMPLETE
            || $order->getOrigData('state') === Order::STATE_COMPLETE; //credit memo of completed order
    }
}
