<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\SalesRule\Model\Quote\Discount;

use Amasty\Mostviewed\Model\Customer\CustomerGroupContextInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Quote\Discount as SalesRuleDiscountCollector;

class InitializeCustomerGroupId
{
    /**
     * @var CustomerGroupContextInterface
     */
    private $customerGroupContext;

    public function __construct(
        CustomerGroupContextInterface $customerGroupContext
    ) {
        $this->customerGroupContext = $customerGroupContext;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollect(
        SalesRuleDiscountCollector $subject,
        SalesRuleDiscountCollector $result,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): SalesRuleDiscountCollector {
        $this->customerGroupContext->set((int)$quote->getCustomerGroupId());

        return $result;
    }
}
