<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Customer\Frontend;

use Amasty\Mostviewed\Model\Customer\CustomerGroupContextInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class CustomerGroupContext implements CustomerGroupContextInterface
{
    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var int|null
     */
    private $customerGroupId;

    /**
     * @var bool
     */
    private $initialized = false;

    public function __construct(CustomerSessionFactory $customerSessionFactory)
    {
        $this->customerSessionFactory = $customerSessionFactory;
    }

    public function set(?int $customerGroupId): void
    {
        if (!$this->initialized) {
            $this->customerGroupId = $customerGroupId;
            $this->initialized = true;
        }
    }

    public function get(): ?int
    {
        if (!$this->initialized) {
            $this->set((int)$this->customerSessionFactory->create()->getCustomerGroupId());
        }
        return $this->customerGroupId;
    }
}
