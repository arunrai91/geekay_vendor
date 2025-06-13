<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Customer;

class CustomerGroupContext implements CustomerGroupContextInterface
{
    /**
     * @var int|null
     */
    private $customerGroupId;

    public function set(?int $customerGroupId): void
    {
        $this->customerGroupId = $customerGroupId;
    }

    public function get(): ?int
    {
        return $this->customerGroupId;
    }
}
