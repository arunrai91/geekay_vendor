<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface;

interface DetailLoaderInterface
{
    /**
     * Load associated VisitHistoryDetails via VisitHistory ID.
     *
     * @param int $visitHistoryId
     * @return VisitHistoryDetailInterface[]
     */
    public function loadDetails(int $visitHistoryId): array;
}
