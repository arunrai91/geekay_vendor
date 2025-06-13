<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api\Data;

interface PatchTablesDataInterface
{
    public const BLOCKS_TO_DELETE = 'blocks_to_delete';
    public const BLOCKS_TO_INSERT = 'blocks_to_insert';
    public const BLOCKS_V6_TO_DELETE = 'blocks_v6_to_delete';
    public const BLOCKS_V6_TO_INSERT = 'blocks_v6_to_insert';
    public const LOCATIONS_TO_DELETE = 'locations_to_delete';
    public const LOCATIONS_TO_INSERT = 'locations_to_insert';

    /**
     * @return BlockInterface[]
     */
    public function getBlocksToDelete(): array;

    /**
     * @param BlockInterface[] $blocks
     * @return void
     */
    public function setBlocksToDelete(array $blocks): void;

    /**
     * @return BlockInterface[]
     */
    public function getBlocksToInsert(): array;

    /**
     * @param BlockInterface[] $blocks
     * @return void
     */
    public function setBlocksToInsert(array $blocks): void;

    /**
     * @return BlockV6Interface[]
     */
    public function getBlocksV6ToDelete(): array;

    /**
     * @param BlockV6Interface[] $blocks
     * @return void
     */
    public function setBlockV6ToDelete(array $blocks): void;

    /**
     * @return BlockV6Interface[]
     */
    public function getBlocksV6ToInsert(): array;

    /**
     * @param BlockV6Interface[] $blocks
     * @return void
     */
    public function setBlocksV6ToInsert(array $blocks): void;

    /**
     * @return LocationInterface[]
     */
    public function getLocationsToDelete(): array;

    /**
     * @param LocationInterface[] $locations
     * @return void
     */
    public function setLocationsToDelete(array $locations): void;

    /**
     * @return LocationInterface[]
     */
    public function getLocationsToInsert(): array;

    /**
     * @param LocationInterface[] $locations
     * @return void
     */
    public function setLocationsToInsert(array $locations): void;
}
