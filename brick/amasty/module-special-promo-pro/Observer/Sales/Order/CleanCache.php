<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Observer\Sales\Order;

use Amasty\RulesPro\Model\Cache;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CleanCache implements ObserverInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
    }

    public function execute(Observer $observer): self
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order || !($customerId = $order->getCustomerId())) {
            return $this;
        }

        $this->cache->clean([Cache::CACHE_TAG . $customerId]);

        return $this;
    }
}
