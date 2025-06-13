<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api;

use Amasty\Geoip\Api\Data\LocationInterface;

interface LocationRepositoryInterface
{
    /**
     * @param LocationInterface[] $locations
     * @return int
     */
    public function deleteByLocId(array $locations): int;

    /**
     * @param LocationInterface[] $locations
     * @return int
     */
    public function insertMultiple(array $locations): int;
}
