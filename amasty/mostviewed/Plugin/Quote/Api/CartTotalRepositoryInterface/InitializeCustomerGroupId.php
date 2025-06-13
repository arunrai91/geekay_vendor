<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Quote\Api\CartTotalRepositoryInterface;

use Amasty\Mostviewed\Model\Customer\CustomerGroupContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

class InitializeCustomerGroupId
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CustomerGroupContextInterface
     */
    private $customerGroupContext;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CustomerGroupContextInterface $customerGroupContext
    ) {
        $this->cartRepository = $cartRepository;
        $this->customerGroupContext = $customerGroupContext;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param CartTotalRepositoryInterface $subject
     * @param int $cartId
     */
    public function beforeGet(CartTotalRepositoryInterface $subject, $cartId): void
    {
        $quote = $this->cartRepository->getActive($cartId);
        $this->customerGroupContext->set((int)$quote->getCustomerGroupId());
    }
}
