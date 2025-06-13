<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model;

use Amasty\Geoip\Api\Data\LocationInterface;
use Amasty\Geoip\Model\ResourceModel\Location as LocationResource;
use Magento\Framework\Model\AbstractModel;

class Location extends AbstractModel implements LocationInterface
{
    protected function _construct()
    {
        $this->_init(LocationResource::class);
    }

    public function getId(): ?int
    {
        $id = $this->_getData(self::LOCATION_ID);

        if (!$id) {
            return null;
        }

        return (int)$id;
    }

    public function getGeoipLocId(): string
    {
        return (string)$this->_getData(self::GEOIP_LOC_ID);
    }

    public function setGeoipLocId(string $geoipLocId): void
    {
        $this->setData(self::GEOIP_LOC_ID, $geoipLocId);
    }

    public function getCountry(): ?string
    {
        return $this->hasData(self::COUNTRY)
            ? (string)$this->_getData(self::COUNTRY)
            : null;
    }

    public function setCountry(?string $country): void
    {
        $this->setData(self::COUNTRY, $country);
    }

    public function getCity(): ?string
    {
        return $this->hasData(self::CITY)
            ? (string)$this->_getData(self::CITY)
            : null;
    }

    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    public function getRegion(): ?string
    {
        return $this->hasData(self::REGION)
            ? (string)$this->_getData(self::REGION)
            : null;
    }

    public function setRegion(string $region): void
    {
        $this->setData(self::REGION, $region);
    }
}
