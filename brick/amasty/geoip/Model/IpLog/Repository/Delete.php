<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\IpLog\Repository;

use Amasty\Geoip\Api\IpLog\DeleteInterface;
use Amasty\Geoip\Model\ResourceModel\IpLog as IpLogResource;

class Delete implements DeleteInterface
{
    /**
     * @var IpLogResource
     */
    private $ipLogResource;

    public function __construct(
        IpLogResource $ipLogResource
    ) {
        $this->ipLogResource = $ipLogResource;
    }

    public function deleteByLastVisitOlderThan(string $date): int
    {
        return $this->ipLogResource->deleteByLastVisitOlderThan($date);
    }
}
