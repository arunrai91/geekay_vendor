<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Observer;

use Amasty\Acart\Model\RuleQuoteFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CheckoutCartSaveAfterObserver implements ObserverInterface
{
    /**
     * @var RuleQuoteFactory
     */
    private $ruleQuoteFactory;

    public function __construct(
        RuleQuoteFactory $ruleQuoteFactory
    ) {
        $this->ruleQuoteFactory = $ruleQuoteFactory;
    }

    public function execute(EventObserver $observer)
    {
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getCart()->getQuote();

        /** @var \Amasty\Acart\Model\RuleQuote $ruleQuote */
        $ruleQuote = $this->ruleQuoteFactory->create();
        $ruleQuote->updateQuote((int)$quote->getId());
    }
}
