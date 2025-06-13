<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Customer;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class CustomerGroup extends Common
{
    public const CATEGORY = 'customer/group/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'tax_class_name'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Customer\Model\Group $group */
        $group = $metadata->getObject();

        return [
            LogEntry::ITEM => $group->getCustomerGroupCode(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Customer Group'),
            LogEntry::ELEMENT_ID => (int)$group->getId(),
        ];
    }
}
