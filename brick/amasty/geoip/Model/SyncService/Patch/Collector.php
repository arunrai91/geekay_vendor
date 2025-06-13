<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Patch;

use Amasty\Geoip\Api\BlockRepositoryInterface;
use Amasty\Geoip\Api\BlockV6RepositoryInterface;
use Amasty\Geoip\Api\Data\BlockInterface;
use Amasty\Geoip\Api\Data\BlockV6Interface;
use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Api\Data\PatchTablesDataInterface;
use Amasty\Geoip\Model\SyncService\Client;

class Collector
{
    /**
     * @var TablesDataFactory
     */
    private $patchTablesDataFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var BlockV6RepositoryInterface
     */
    private $blockV6Repository;

    /**
     * @var Client
     */
    private $client;

    public function __construct(
        TablesDataFactory $patchTablesDataFactory,
        BlockRepositoryInterface $blockRepository,
        BlockV6RepositoryInterface $blockV6Repository,
        Client $client
    ) {
        $this->patchTablesDataFactory = $patchTablesDataFactory;
        $this->blockRepository = $blockRepository;
        $this->blockV6Repository = $blockV6Repository;
        $this->client = $client;
    }

    /**
     * @param IpLogInterface[] $ipLogs
     * @return PatchTablesDataInterface
     */
    public function collect(array $ipLogs): PatchTablesDataInterface
    {
        $patchTablesData = $this->patchTablesDataFactory->create();

        $blocksToDelete = $this->collectBlocksToDelete($ipLogs);
        $blocksV6ToDelete = $this->collectBlocksV6ToDelete($ipLogs);
        $dataToInsert = $this->client->requestDataToInsert(array_merge($blocksToDelete, $blocksV6ToDelete));

        $patchTablesData->setBlocksToDelete($blocksToDelete);
        $patchTablesData->setBlockV6ToDelete($blocksV6ToDelete);

        $patchTablesData->setBlocksToInsert($dataToInsert[Client::BLOCK_RESPONSE_KEY]);
        $patchTablesData->setBlocksV6ToInsert($dataToInsert[Client::BLOCK_V6_RESPONSE_KEY]);
        $patchTablesData->setLocationsToDelete($dataToInsert[Client::LOCATION_RESPONSE_KEY]);
        $patchTablesData->setLocationsToInsert($dataToInsert[Client::LOCATION_RESPONSE_KEY]);

        return $patchTablesData;
    }

    /**
     * @param IpLogInterface[] $ipLogs
     * @return BlockInterface[]
     */
    private function collectBlocksToDelete(array $ipLogs): array
    {
        return $this->blockRepository->getByIpLogs(
            $this->filterLogsByIpVersion($ipLogs, IpLogInterface::IP_V_4)
        );
    }

    /**
     * @param IpLogInterface[] $ipLogs
     * @return BlockV6Interface[]
     */
    private function collectBlocksV6ToDelete(array $ipLogs): array
    {
        return $this->blockV6Repository->getByIpLogs(
            $this->filterLogsByIpVersion($ipLogs, IpLogInterface::IP_V_6)
        );
    }

    private function filterLogsByIpVersion(array $ipLogs, int $ipVersion): array
    {
        return array_filter($ipLogs, static function ($ipLog) use ($ipVersion) {
            return $ipLog->getIpVersion() === $ipVersion;
        });
    }
}
