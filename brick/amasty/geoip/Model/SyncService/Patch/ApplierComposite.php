<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\SyncService\Patch;

use Amasty\Geoip\Api\Data\PatchTablesDataInterface;
use Amasty\Geoip\Api\TablePatchApplierInterface;

class ApplierComposite implements TablePatchApplierInterface
{
    /**
     * @var TablePatchApplierInterface[]
     */
    private $appliersPool;

    public function __construct(
        array $appliersPool = []
    ) {
        $this->appliersPool = $appliersPool;
    }

    public function apply(PatchTablesDataInterface $patchData): void
    {
        foreach ($this->appliersPool as $applier) {
            $applier->apply($patchData);
        }
    }
}
