<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Observer;

use Amasty\RulesPro\Model\Queue\QueueRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * event: "customer_login"
 *
 * Add $customerId to queue for cache warming
 */
class CacheWarmer implements ObserverInterface
{
    /**
     * @var QueueRepository
     */
    private $queueRepository;

    public function __construct(
        QueueRepository $queueRepository
    ) {
        $this->queueRepository = $queueRepository;
    }

    public function execute(Observer $observer): void
    {
        $customerId = (int)$observer->getCustomer()->getId();
        $this->queueRepository->saveByCustomerId($customerId);
    }
}
