<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Visit History Entry search results.
 */
class VisitHistoryEntrySearchResults extends SearchResults implements VisitHistoryEntrySearchResultsInterface
{
}
