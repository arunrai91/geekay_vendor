<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Flag extends Common
{
    public function getLogMetadata(MetadataInterface $metadata): array
    {
        $flag = $metadata->getObject();

        return [
            LogEntry::ITEM => $flag->getFlagCode(),
            LogEntry::CATEGORY => __('Flag'),
            LogEntry::CATEGORY_NAME => __('Flag'),
            LogEntry::ELEMENT_ID => (int)$flag->getId()
        ];
    }
}
