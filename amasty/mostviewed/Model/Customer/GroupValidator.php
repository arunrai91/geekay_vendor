<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Customer;

use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;

class GroupValidator
{
    /**
     * @var CustomerGroupContextInterface|null
     */
    private $customerGroupContext;

    public function __construct(
        ?CustomerSessionFactory $customerSessionFactory = null, // @deprecated
        ?CustomerGroupContextInterface $customerGroupContext = null
    ) {
        $this->customerGroupContext = $customerGroupContext ?? ObjectManager::getInstance()->get(
            CustomerGroupContextInterface::class
        );
    }

    public function validate(AbstractModel $entity): bool
    {
        if (!method_exists($entity, 'getCustomerGroupIds')) {
            return false;
        }

        $currentCustomerGroup = $this->customerGroupContext->get() ?: GroupManagement::NOT_LOGGED_IN_ID;
        $customerGroups = $entity->getCustomerGroupIds();
        $customerGroups = explode(',', $customerGroups);

        return in_array($currentCustomerGroup, $customerGroups)
            || in_array(CustomerGroupInterface::CUST_GROUP_ALL, $customerGroups);
    }
}
