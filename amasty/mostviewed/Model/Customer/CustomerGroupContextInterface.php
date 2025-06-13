<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Customer;

interface CustomerGroupContextInterface
{
    public function set(?int $customerGroupId): void;

    public function get(): ?int;
}
