<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Filter;

use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer\Customer;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;

class FilterCustomerListing implements CollectionModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function apply(AbstractDb $collection)
    {
        $customerId = (int)$this->request->getParam('parent_id');
        if (!$customerId) {
            return;
        }

        $collection->getSelect()->where(sprintf(
            'main_table.%s = \'%s\' AND main_table.%s = %s',
            LogEntry::CATEGORY,
            Customer::CATEGORY,
            LogEntry::ELEMENT_ID,
            $customerId
        ));
    }
}
