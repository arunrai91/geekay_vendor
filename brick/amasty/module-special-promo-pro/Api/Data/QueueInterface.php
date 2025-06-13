<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Api\Data;

interface QueueInterface
{
    public const QUEUE_ID = 'queue_id';
    public const CUSTOMER_ID = 'customer_id';

    /**
     * @return int|null
     */
    public function getQueueId(): ?int;

    /**
     * @param int|null $queueId
     *
     * @return $this
     */
    public function setQueueId(?int $queueId): QueueInterface;

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @param int|null $customerId
     *
     * @return $this
     */
    public function setCustomerId(?int $customerId): QueueInterface;
}
