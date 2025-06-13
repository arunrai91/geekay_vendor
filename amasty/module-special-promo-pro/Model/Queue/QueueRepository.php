<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Queue;

use Amasty\RulesPro\Api\Data\QueueInterface;
use Amasty\RulesPro\Model\Queue\QueueFactory;
use Amasty\RulesPro\Model\ResourceModel\Queue as ResourceQueue;
use Amasty\RulesPro\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class QueueRepository
{
    /**
     * @var ResourceQueue
     */
    private $resourceQueue;

    public function __construct(
        ResourceQueue $resourceQueue
    ) {
        $this->resourceQueue = $resourceQueue;
    }

    public function save(QueueInterface $queueEntity): void
    {
        try {
            $this->resourceQueue->save($queueEntity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the queue: %1',
                    $exception->getMessage()
                )
            );
        }
    }

    /**
     * @param int[] $queueIds
     */
    public function deleteByIds(array $queueIds): void
    {
        $this->resourceQueue->deleteByIds($queueIds);
    }

    public function saveByCustomerId(int $customerId): void
    {
        $this->resourceQueue->saveByCustomerId($customerId);
    }
}
