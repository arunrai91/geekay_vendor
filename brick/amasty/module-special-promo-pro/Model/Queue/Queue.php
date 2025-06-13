<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\Queue;

use Amasty\RulesPro\Api\Data\QueueInterface;
use Amasty\RulesPro\Model\ResourceModel\Queue as ResourceQueue;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel implements QueueInterface
{
    protected function _construct()
    {
        $this->_init(ResourceQueue::class);
    }

    public function getQueueId(): ?int
    {
        return ($this->getData(self::QUEUE_ID) === null) ? null
            : (int)$this->getData(self::QUEUE_ID);
    }

    public function setQueueId(?int $queueId): QueueInterface
    {
        return $this->setData(self::QUEUE_ID, $queueId);
    }

    public function getCustomerId(): ?int
    {
        return ($this->getData(self::CUSTOMER_ID) === null) ? null
            : (int)$this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId(?int $customerId): QueueInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
}
