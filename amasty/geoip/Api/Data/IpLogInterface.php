<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api\Data;

interface IpLogInterface
{
    public const LOG_ID = 'log_id';
    public const IP = 'ip';
    public const LAST_VISIT = 'last_visit';
    public const LAST_SYNC = 'last_sync';
    public const IP_V_4 = 4;
    public const IP_V_6 = 6;

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getIp(): string;

    /**
     * @param string $ip
     * @return void
     */
    public function setIp(string $ip): void;

    /**
     * @return int
     */
    public function getIpVersion(): int;

    /**
     * @return string
     */
    public function getLastVisit(): string;

    /**
     * @param string $lastVisit
     * @return void
     */
    public function setLastVisit(string $lastVisit): void;

    /**
     * @return string|null
     */
    public function getLastSync(): ?string;

    /**
     * @param string $lastSync
     * @return void
     */
    public function setLastSync(string $lastSync): void;

    /**
     * @param array $keys
     * @return array
     */
    public function toArray(array $keys = []);
}
