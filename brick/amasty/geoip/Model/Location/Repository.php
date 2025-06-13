<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model\Location;

use Amasty\Geoip\Api\Data\LocationInterface;
use Amasty\Geoip\Api\LocationRepositoryInterface;
use Amasty\Geoip\Model\ResourceModel\Location as LocationResource;

class Repository implements LocationRepositoryInterface
{
    /**
     * @var LocationResource
     */
    private $locationResource;

    public function __construct(
        LocationResource $locationResource
    ) {
        $this->locationResource = $locationResource;
    }

    public function deleteByLocId(array $locations): int
    {
        $connection = $this->locationResource->getConnection();
        $geoipLocIds = array_map(static function ($location) {
            return $location->getGeoipLocId();
        }, $locations);

        return $connection->delete(
            $this->locationResource->getMainTable(),
            [LocationInterface::GEOIP_LOC_ID . ' IN (?)' => $geoipLocIds]
        );
    }

    public function insertMultiple(array $locations): int
    {
        return $this->locationResource->getConnection()->insertMultiple(
            $this->locationResource->getMainTable(),
            array_map(static function ($location) {
                return $location->toArray();
            }, $locations)
        );
    }
}
