<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Restoring;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;

interface EntityRestoreHandlerInterface
{
    /**
     * Perform restoring action by action log details
     *
     * @param \Amasty\AdminActionsLog\Api\Data\LogEntryInterface $logEntry
     * @param \Amasty\AdminActionsLog\Api\Data\LogDetailInterface[] $logDetails
     * @return void
     */
    public function restore(LogEntryInterface $logEntry, array $logDetails): void;
}
