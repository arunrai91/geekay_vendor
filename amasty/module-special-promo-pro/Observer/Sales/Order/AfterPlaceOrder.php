<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Observer\Sales\Order;

use Amasty\RulesPro\Api\RuleUsageRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * for save the used rule to counter, 'sales_order_place_after' event
 */
class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var RuleUsageRepositoryInterface
     */
    private $ruleUsageRepository;

    public function __construct(
        RuleUsageRepositoryInterface $ruleUsageRepository
    ) {
        $this->ruleUsageRepository = $ruleUsageRepository;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order || !$order->getAppliedRuleIds()) {
            return $this;
        }

        $ruleIds = $this->getRuleIds($order);

        $this->ruleUsageRepository->incrementUsageCountByRuleIds($ruleIds);

        return $this;
    }

    /**
     * @param $order
     *
     * @return array
     */
    private function getRuleIds($order)
    {
        $ruleIds = explode(',', $order->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);

        return $ruleIds;
    }
}
