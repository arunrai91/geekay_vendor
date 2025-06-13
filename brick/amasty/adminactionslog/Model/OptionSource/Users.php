<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\OptionSource;

use Magento\User\Model\ResourceModel\User\CollectionFactory;

class Users implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $adminCollectionFactory;

    public function __construct(
        CollectionFactory $adminCollectionFactory
    ) {
        $this->adminCollectionFactory = $adminCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        $adminCollection = $this->adminCollectionFactory->create();
        $adminCollection->addFieldToFilter('main_table.is_active', 1)
            ->addFieldToSelect(['user_id', 'firstname', 'lastname', 'username']);

        foreach ($adminCollection->getData() as $admin) {
            $result[$admin['user_id']] = $admin['firstname'] . ' ' . $admin['lastname']
                . ' (' . $admin['username'] . ')';
        }

        return $result;
    }
}
