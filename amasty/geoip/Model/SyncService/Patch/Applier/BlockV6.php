<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Patch\Applier;

use Amasty\Geoip\Api\BlockV6RepositoryInterface;
use Amasty\Geoip\Api\Data\PatchTablesDataInterface;
use Amasty\Geoip\Api\TablePatchApplierInterface;

class BlockV6 implements TablePatchApplierInterface
{
    /**
     * @var BlockV6RepositoryInterface
     */
    private $blockV6Repository;

    public function __construct(
        BlockV6RepositoryInterface $blockV6Repository
    ) {
        $this->blockV6Repository = $blockV6Repository;
    }

    public function apply(PatchTablesDataInterface $patchData): void
    {
        $this->blockV6Repository->deleteByStartAndEndIpNum($patchData->getBlocksV6ToDelete());
        $this->blockV6Repository->insertMultiple($patchData->getBlocksV6ToInsert());
    }
}
