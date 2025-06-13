<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api;

use Amasty\Geoip\Api\Data\BlockInterface;
use Amasty\Geoip\Api\Data\IpLogInterface;

interface BlockRepositoryInterface
{
    /**
     * @param IpLogInterface[] $ipLogs
     * @return BlockInterface[]
     */
    public function getByIpLogs(array $ipLogs): array;

    /**
     * @param BlockInterface[] $blocks
     * @return int
     */
    public function deleteByStartAndEndIpNum(array $blocks): int;

    /**
     * @param BlockInterface[] $blocks
     * @return int
     */
    public function insertMultiple(array $blocks): int;
}
