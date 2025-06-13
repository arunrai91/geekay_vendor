<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\ResourceModel\Block;

use Amasty\Geoip\Api\Data\BlockInterface;
use Amasty\Geoip\Model\Block;
use Amasty\Geoip\Model\ResourceModel\Block as BlockResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Block::class, BlockResource::class);
    }

    public function addFilterByLongIp(string $longIp): void
    {
        $condition = sprintf(
            '%s <= ? AND %s >= ?',
            BlockInterface::START_IP_NUM,
            BlockInterface::END_IP_NUM
        );

        $where = $this->getConnection()->quoteInto($condition, $longIp);
        $this->getSelect()
            ->oRwhere($where);
    }
}
