<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api\IpLog;

interface DeleteInterface
{
    /**
     * @param string $date
     * @return int
     */
    public function deleteByLastVisitOlderThan(string $date): int;
}
