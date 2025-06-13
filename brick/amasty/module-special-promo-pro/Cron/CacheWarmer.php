<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Cron;

use Amasty\RulesPro\Model\Queue\QueueGetList;
use Amasty\RulesPro\Model\Queue\QueueProcessor;
use Amasty\RulesPro\Model\Queue\QueueRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CacheWarmer
{
    private const BATCH_SIZE = 1000;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var QueueGetList
     */
    private $queueGetList;

    public function __construct(
        QueueRepository $queueRepository,
        QueueProcessor $queueProcessor,
        QueueGetList $queueGetList,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->queueRepository = $queueRepository;
        $this->queueProcessor = $queueProcessor;
        $this->queueGetList = $queueGetList;
        $this->searchCriteriaBuilder =  $searchCriteriaBuilder;
    }

    public function execute()
    {
        $customerIds = [];
        $queueIds = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setPageSize(self::BATCH_SIZE);

        foreach ($this->queueGetList->getList($searchCriteria)->getItems() as $queueEntity) {
            $customerIds[] = $queueEntity->getCustomerId();
            $queueIds[] = $queueEntity->getQueueId();
        }

        if (count($customerIds) > 0) {
            $this->queueProcessor->process($customerIds);
            $this->queueRepository->deleteByIds($queueIds);
        }
    }
}
