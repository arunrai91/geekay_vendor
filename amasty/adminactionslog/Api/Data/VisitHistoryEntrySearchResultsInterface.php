<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

interface VisitHistoryEntrySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get visit history list.
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface[]
     */
    public function getItems();

    /**
     * Set visit history list.
     *
     * @param \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
