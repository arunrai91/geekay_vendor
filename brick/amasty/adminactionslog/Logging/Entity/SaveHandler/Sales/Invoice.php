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

class Invoice extends Common
{
    public const CATEGORY = 'sales/order_invoice/view';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $metadata->getObject();
        $type = $invoice->getOrigData() === null ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __('Invoice for Order #%1', $invoice->getOrder()->getRealOrderId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Invoice'),
            LogEntry::ELEMENT_ID => (int)$invoice->getId(),
            LogEntry::PARAMETER_NAME => 'invoice_id'
        ];
    }
}
