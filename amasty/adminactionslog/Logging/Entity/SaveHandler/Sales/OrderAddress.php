<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Sales;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class OrderAddress extends Common
{
    public const CATEGORY = 'sales/order/address';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        'form_key',
        'region_code'
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $metadata->getObject();
        $type = $address->getOrigData() === null ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __(
                '%1 Address for Order #%2',
                ucfirst($address->getAddressType()),
                $address->getOrder()->getRealOrderId()
            ),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Order Address'),
            LogEntry::ELEMENT_ID => (int)$address->getId(),
            LogEntry::PARAMETER_NAME => 'address_id'
        ];
    }
}
