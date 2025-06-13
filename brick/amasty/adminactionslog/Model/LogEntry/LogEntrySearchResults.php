<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LogEntry;

use Amasty\AdminActionsLog\Api\Data\LogEntrySearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Log Entry search results.
 */
class LogEntrySearchResults extends SearchResults implements LogEntrySearchResultsInterface
{
}
