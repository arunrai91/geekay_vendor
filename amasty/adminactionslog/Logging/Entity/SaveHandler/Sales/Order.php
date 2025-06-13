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

class Order extends Common
{
    public const CATEGORY = 'sales/order/view';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $metadata->getObject();
        $type = $order->getOrigData() === null ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __('Order #%1', $order->getRealOrderId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Order'),
            LogEntry::ELEMENT_ID => (int)$order->getId(),
            LogEntry::PARAMETER_NAME => 'order_id'
        ];
    }
}
