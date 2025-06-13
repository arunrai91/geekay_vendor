<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface VisitHistoryEntryFinderInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VisitHistoryEntrySearchResultsInterface;
}
