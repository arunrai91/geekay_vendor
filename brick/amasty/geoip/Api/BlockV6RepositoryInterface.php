<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api;

use Amasty\Geoip\Api\Data\BlockV6Interface;
use Amasty\Geoip\Api\Data\IpLogInterface;

interface BlockV6RepositoryInterface
{
    /**
     * @param IpLogInterface[] $ipLog
     * @return BlockV6Interface[]
     */
    public function getByIpLogs(array $ipLogs): array;

    /**
     * @param BlockV6Interface[] $blocks
     * @return int
     */
    public function deleteByStartAndEndIpNum(array $blocks): int;

    /**
     * @param BlockV6Interface[] $blocks
     * @return int
     */
    public function insertMultiple(array $blocks): int;
}
