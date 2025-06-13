<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Patch\Applier;

use Amasty\Geoip\Api\BlockRepositoryInterface;
use Amasty\Geoip\Api\Data\PatchTablesDataInterface;
use Amasty\Geoip\Api\TablePatchApplierInterface;

class Block implements TablePatchApplierInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    public function __construct(
        BlockRepositoryInterface $blockRepository
    ) {
        $this->blockRepository = $blockRepository;
    }

    public function apply(PatchTablesDataInterface $patchData): void
    {
        $this->blockRepository->deleteByStartAndEndIpNum($patchData->getBlocksToDelete());
        $this->blockRepository->insertMultiple($patchData->getBlocksToInsert());
    }
}
