<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterface;
use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntrySearchResultsInterfaceFactory;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryFinderInterface;
use Amasty\AdminActionsLog\Api\VisitHistoryEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\CriteriaApplierTrait;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements VisitHistoryEntryFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var VisitHistoryEntryRepositoryInterface
     */
    private $historyEntryRepository;

    /**
     * @var VisitHistoryEntrySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        VisitHistoryEntryRepositoryInterface $historyEntryRepository,
        VisitHistoryEntrySearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->historyEntryRepository = $historyEntryRepository;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): VisitHistoryEntrySearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $visitHistoryCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $visitHistoryCollection);
        $searchResults->setTotalCount($visitHistoryCollection->getSize());
        $historyItems = array_map(function (VisitHistoryEntryInterface $visitHistory) {
            return $this->historyEntryRepository->getById($visitHistory->getId());
        }, $visitHistoryCollection->getItems());
        $searchResults->setItems($historyItems);

        return $searchResults;
    }
}
