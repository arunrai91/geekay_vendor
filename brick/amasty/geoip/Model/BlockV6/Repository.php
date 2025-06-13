<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\BlockV6;

use Amasty\Geoip\Api\BlockV6RepositoryInterface;
use Amasty\Geoip\Api\Data\BlockV6Interface;
use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Helper\Data as DataHelper;
use Amasty\Geoip\Model\BlockV6Factory;
use Amasty\Geoip\Model\ResourceModel\BlockV6 as BlockV6Resource;
use Amasty\Geoip\Model\ResourceModel\BlockV6\CollectionFactory;
use Magento\Framework\DB\Select;

class Repository implements BlockV6RepositoryInterface
{
    /**
     * @var BlockV6Resource
     */
    private $blockV6Resource;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var CollectionFactory
     */
    private $blockV6CollectionFactory;

    /**
     * @var BlockV6Factory
     */
    private $blockV6Factory;

    public function __construct(
        BlockV6Resource $blockV6Resource,
        DataHelper $dataHelper,
        BlockV6Factory $blockV6Factory,
        CollectionFactory $blockV6CollectionFactory
    ) {
        $this->blockV6Resource = $blockV6Resource;
        $this->dataHelper = $dataHelper;
        $this->blockV6Factory = $blockV6Factory;
        $this->blockV6CollectionFactory = $blockV6CollectionFactory;
    }

    public function getByIpLogs(array $ipLogs): array
    {
        if (empty($ipLogs)) {
            return [];
        }

        $collection = $this->blockV6CollectionFactory->create();

        foreach ($ipLogs as $ipLog) {
            $collection->addFilterByLongIp($this->getLongIp($ipLog));
        }

        return array_values($collection->getItems());
    }

    private function getLongIp(IpLogInterface $ipLog): string
    {
        return $this->dataHelper->getLongIpV6($ipLog->getIp());
    }

    public function deleteByStartAndEndIpNum(array $blocks): int
    {
        if (empty($blocks)) {
            return 0;
        }

        $connection = $this->blockV6Resource->getConnection();
        $select = $connection->select();

        foreach ($blocks as $block) {
            $select->orWhere(
                $connection->quoteInto(BlockV6Interface::START_IP_NUM . ' = ?', $block->getStartIpNum())
                . $connection->quoteInto(' AND ' . BlockV6Interface::END_IP_NUM . ' = ?', $block->getEndIpNum())
            );
        }

        return $connection->delete(
            $this->blockV6Resource->getMainTable(),
            implode(' ', $select->getPart(Select::WHERE))
        );
    }

    public function insertMultiple(array $blocks): int
    {
        if (empty($blocks)) {
            return 0;
        }

        return $this->blockV6Resource->getConnection()->insertMultiple(
            $this->blockV6Resource->getMainTable(),
            array_map(static function ($block) {
                return $block->toArray();
            }, $blocks)
        );
    }
}
