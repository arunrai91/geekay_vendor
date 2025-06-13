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

class Creditmemo extends Common
{
    public const CATEGORY = 'sales/order_creditmemo/view';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditMemo */
        $creditMemo = $metadata->getObject();
        $type = $creditMemo->getOrigData() === null ? LogEntryTypes::TYPE_NEW : LogEntryTypes::TYPE_EDIT;

        return [
            LogEntry::TYPE => $type,
            LogEntry::ITEM => __('Credit Memo for Order #%1', $creditMemo->getOrder()->getRealOrderId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('Credit Memo'),
            LogEntry::ELEMENT_ID => (int)$creditMemo->getId(),
            LogEntry::PARAMETER_NAME => 'creditmemo_id'
        ];
    }
}
