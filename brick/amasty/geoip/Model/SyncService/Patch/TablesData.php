<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Patch;

use Amasty\Geoip\Api\Data\PatchTablesDataInterface;
use Magento\Framework\DataObject;

class TablesData extends DataObject implements PatchTablesDataInterface
{
    public function getBlocksToDelete(): array
    {
        return $this->getDataOrEmptyArray(self::BLOCKS_TO_DELETE);
    }

    public function setBlocksToDelete(array $blocks): void
    {
        $this->setData(self::BLOCKS_TO_DELETE, $blocks);
    }

    public function getBlocksToInsert(): array
    {
        return $this->getDataOrEmptyArray(self::BLOCKS_TO_INSERT);
    }

    public function setBlocksToInsert(array $blocks): void
    {
        $this->setData(self::BLOCKS_TO_INSERT, $blocks);
    }

    public function getBlocksV6ToDelete(): array
    {
        return $this->getDataOrEmptyArray(self::BLOCKS_V6_TO_DELETE);
    }

    public function setBlockV6ToDelete(array $blocks): void
    {
        $this->setData(self::BLOCKS_V6_TO_DELETE, $blocks);
    }

    public function getBlocksV6ToInsert(): array
    {
        return $this->getDataOrEmptyArray(self::BLOCKS_V6_TO_INSERT);
    }

    public function setBlocksV6ToInsert(array $blocks): void
    {
        $this->setData(self::BLOCKS_V6_TO_INSERT, $blocks);
    }

    public function getLocationsToDelete(): array
    {
        return $this->getDataOrEmptyArray(self::LOCATIONS_TO_DELETE);
    }

    public function setLocationsToDelete(array $locations): void
    {
        $this->setData(self::LOCATIONS_TO_DELETE, $locations);
    }

    public function getLocationsToInsert(): array
    {
        return $this->getDataOrEmptyArray(self::LOCATIONS_TO_INSERT);
    }

    public function setLocationsToInsert(array $locations): void
    {
        $this->setData(self::LOCATIONS_TO_INSERT, $locations);
    }

    private function getDataOrEmptyArray(string $key): array
    {
        $data = $this->_getData($key);

        return is_array($data) ? $data : [];
    }
}
