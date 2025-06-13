<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model;

use Amasty\Geoip\Api\Data\BlockV6Interface;
use Amasty\Geoip\Model\ResourceModel\BlockV6 as BlockV6Resource;
use Magento\Framework\Model\AbstractModel;

class BlockV6 extends AbstractModel implements BlockV6Interface
{
    protected function _construct()
    {
        $this->_init(BlockV6Resource::class);
    }

    public function getId(): ?int
    {
        $id = $this->_getData(self::BLOCK_ID);

        if (!$id) {
            return null;
        }

        return (int)$id;
    }

    public function getStartIpNum(): string
    {
        return (string)$this->_getData(self::START_IP_NUM);
    }

    public function setStartIpNum(string $startIpNum): void
    {
        $this->setData(self::START_IP_NUM, $startIpNum);
    }

    public function getEndIpNum(): string
    {
        return (string)$this->_getData(self::END_IP_NUM);
    }

    public function setEndIpNum(string $endIpNum): void
    {
        $this->setData(self::END_IP_NUM, $endIpNum);
    }

    public function getGeoipLocId(): string
    {
        return (string)$this->_getData(self::GEOIP_LOC_ID);
    }

    public function setGeoipLocId(string $geoipLocId): void
    {
        $this->setData(self::GEOIP_LOC_ID, $geoipLocId);
    }

    public function getPostalCode(): ?string
    {
        return $this->hasData(self::POSTAL_CODE)
            ? (string)$this->getData(self::POSTAL_CODE)
            : null;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->setData(self::POSTAL_CODE, $postalCode);
    }

    public function getLatitude(): ?string
    {
        return $this->hasData(self::LATITUDE)
            ? (string)$this->_getData(self::LATITUDE)
            : null;
    }

    public function setLatitude(?string $latitude): void
    {
        $this->setData(self::LATITUDE, $latitude);
    }

    public function getLongitude(): ?string
    {
        return $this->hasData(self::LONGITUDE)
            ? (string)$this->_getData(self::LONGITUDE)
            : null;
    }

    public function setLongitude(?string $longitude): void
    {
        $this->setData(self::LONGITUDE, $longitude);
    }
}
