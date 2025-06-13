<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Queue;

use Amasty\RulesPro\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;

class QueueGetList
{
    /**
     * @var CollectionFactory
     */
    private $queueCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        CollectionFactory $queueCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->queueCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
