<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api;

use Amasty\Geoip\Api\Data\PatchTablesDataInterface;

interface TablePatchApplierInterface
{
    public function apply(PatchTablesDataInterface $patchData): void;
}
