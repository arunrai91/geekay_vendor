<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Model;

use Amasty\Geoip\Api\Data\IpLogInterface;
use Amasty\Geoip\Model\ResourceModel\IpLog as IpLogResource;
use Magento\Framework\Model\AbstractModel;

class IpLog extends AbstractModel implements IpLogInterface
{
    protected function _construct()
    {
        $this->_init(IpLogResource::class);
    }

    public function getId(): ?int
    {
        $id = $this->_getData(self::LOG_ID);

        if (!$id) {
            return null;
        }

        return (int)$id;
    }

    public function getIp(): string
    {
        return (string)$this->_getData(self::IP);
    }

    public function setIp(string $ip): void
    {
        $this->setData(self::IP, $ip);
    }

    public function getIpVersion(): int
    {
        return (bool)filter_var($this->getIp(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? self::IP_V_6
            : self::IP_V_4;
    }

    public function getLastVisit(): string
    {
        return (string)$this->_getData(self::LAST_VISIT);
    }

    public function setLastVisit(string $lastVisit): void
    {
        $this->setData(self::LAST_VISIT, $lastVisit);
    }

    public function getLastSync(): ?string
    {
        $lastSync = $this->_getData(self::LAST_SYNC);

        if (!$lastSync) {
            return null;
        }

        return (string)$lastSync;
    }

    public function setLastSync(string $lastSync): void
    {
        $this->setData(self::LAST_SYNC, $lastSync);
    }
}
