<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Model;

use Amasty\Acart\Api\Data\BlacklistSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class BlacklistSearchResults extends SearchResults implements BlacklistSearchResultsInterface
{
}
