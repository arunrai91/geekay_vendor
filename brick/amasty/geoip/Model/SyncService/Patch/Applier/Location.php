<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Patch\Applier;

use Amasty\Geoip\Api\Data\PatchTablesDataInterface;
use Amasty\Geoip\Api\LocationRepositoryInterface;
use Amasty\Geoip\Api\TablePatchApplierInterface;

class Location implements TablePatchApplierInterface
{
    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    public function __construct(
        LocationRepositoryInterface $locationRepository
    ) {
        $this->locationRepository = $locationRepository;
    }

    public function apply(PatchTablesDataInterface $patchData): void
    {
        $this->locationRepository->deleteByLocId($patchData->getLocationsToDelete());
        $this->locationRepository->insertMultiple($patchData->getLocationsToInsert());
    }
}
