<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\Block;

use Amasty\Geoip\Api\BlockRepositoryInterface;
use Amasty\Geoip\Api\Data\BlockInterface;
use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Model\ResourceModel\Block as BlockResource;
use Amasty\Geoip\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\DB\Select;

class Repository implements BlockRepositoryInterface
{
    /**
     * @var BlockResource
     */
    private $blockResource;

    /**
     * @var CollectionFactory
     */
    private $blockCollectionFactory;

    public function __construct(
        BlockResource $blockResource,
        CollectionFactory $blockCollectionFactory
    ) {
        $this->blockResource = $blockResource;
        $this->blockCollectionFactory = $blockCollectionFactory;
    }

    public function getByIpLogs(array $ipLogs): array
    {
        if (empty($ipLogs)) {
            return [];
        }

        $collection = $this->blockCollectionFactory->create();

        foreach ($ipLogs as $ipLog) {
            $collection->addFilterByLongIp($this->getLongIp($ipLog));
        }

        return array_values($collection->getItems());
    }

    private function getLongIp(IpLogInterface $ipLog): string
    {
        return sprintf("%u", ip2long($ipLog->getIp()));
    }

    public function deleteByStartAndEndIpNum(array $blocks): int
    {
        if (empty($blocks)) {
            return 0;
        }

        $connection = $this->blockResource->getConnection();
        $select = $connection->select();

        foreach ($blocks as $block) {
            $select->orWhere(
                $connection->quoteInto(BlockInterface::START_IP_NUM . ' = ?', $block->getStartIpNum())
                . $connection->quoteInto(' AND ' . BlockInterface::END_IP_NUM . ' = ?', $block->getEndIpNum())
            );
        }

        return $connection->delete(
            $this->blockResource->getMainTable(),
            implode(' ', $select->getPart(Select::WHERE))
        );
    }

    public function insertMultiple(array $blocks): int
    {
        if (empty($blocks)) {
            return 0;
        }

        return $this->blockResource->getConnection()->insertMultiple(
            $this->blockResource->getMainTable(),
            array_map(static function ($block) {
                return $block->toArray();
            }, $blocks)
        );
    }
}
