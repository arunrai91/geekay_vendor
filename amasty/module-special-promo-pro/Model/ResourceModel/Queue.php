<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\ResourceModel;

use Amasty\RulesPro\Api\Data\QueueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_amrules_cache_queue';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, QueueInterface::QUEUE_ID);
    }

    /**
     * @param int[] $queueIds
     */
    public function deleteByIds(array $queueIds): void
    {
        $this->getConnection()->delete($this->getTable(self::MAIN_TABLE), ['queue_id  IN (?)' => $queueIds]);
    }

    public function saveByCustomerId(int $customerId): void
    {
        $query = 'INSERT IGNORE INTO ' . $this->getTable(self::MAIN_TABLE) . ' (customer_id) VALUES (?)';
        $this->getConnection()->query($query, [$customerId]);
    }
}
