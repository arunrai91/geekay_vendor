<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Customer\WebApi;

use Amasty\Mostviewed\Model\Customer\CustomerGroupContextInterface;

class CustomerGroupContext implements CustomerGroupContextInterface
{
    /**
     * @var int|null
     */
    private $customerGroupId;

    /**
     * @var bool
     */
    private $initialized = false;

    public function set(?int $customerGroupId): void
    {
        if (!$this->initialized) {
            $this->customerGroupId = $customerGroupId;
            $this->initialized = true;
        }
    }

    public function get(): ?int
    {
        return $this->customerGroupId;
    }
}
