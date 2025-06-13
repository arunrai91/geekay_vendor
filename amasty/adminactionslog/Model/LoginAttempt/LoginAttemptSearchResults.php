<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Log Entry search results.
 */
class LoginAttemptSearchResults extends SearchResults implements LoginAttemptSearchResultsInterface
{
}
