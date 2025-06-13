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

class Rma extends Common
{
    public const CATEGORY = 'admin/rma/edit';

    /**
     * @param MetadataInterface $metadata
     * @return array
     */
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Rma\Model\Rma $rma */
        $rma = $metadata->getObject();

        return [
            LogEntry::ITEM => __('Return Request #%1', $rma->getId()),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('RMA'),
            LogEntry::ELEMENT_ID => (int)$rma->getId()
        ];
    }
}
