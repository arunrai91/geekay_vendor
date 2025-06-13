<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Special Promotions Pro for Magento 2
 */

namespace Amasty\RulesPro\Model\ResourceModel\Queue;

use Amasty\RulesPro\Model\Queue\Queue;
use Amasty\RulesPro\Model\ResourceModel\Queue as ResourceQueue;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Queue::class, ResourceQueue::class);
    }
}
