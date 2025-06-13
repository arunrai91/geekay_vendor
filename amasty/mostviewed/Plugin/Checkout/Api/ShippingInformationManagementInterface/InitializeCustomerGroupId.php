<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Checkout\Api\ShippingInformationManagementInterface;

use Amasty\Mostviewed\Model\Customer\CustomerGroupContextInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

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
     * @param ShippingInformationManagementInterface $subject
     * @param int $cartId
     */
    public function beforeSaveAddressInformation(ShippingInformationManagementInterface $subject, $cartId): void
    {
        $quote = $this->cartRepository->getActive($cartId);
        $this->customerGroupContext->set((int)$quote->getCustomerGroupId());
    }
}
