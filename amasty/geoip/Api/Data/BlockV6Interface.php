<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api\Data;

interface BlockV6Interface
{
    public const BLOCK_ID = 'block_id';
    public const START_IP_NUM = 'start_ip_num';
    public const END_IP_NUM = 'end_ip_num';
    public const GEOIP_LOC_ID = 'geoip_loc_id';
    public const POSTAL_CODE = 'postal_code';
    public const LATITUDE = 'latitude';
    public const LONGITUDE = 'longitude';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getStartIpNum(): string;

    /**
     * @param string $startIpNum
     * @return void
     */
    public function setStartIpNum(string $startIpNum): void;

    /**
     * @return string
     */
    public function getEndIpNum(): string;

    /**
     * @param string $endIpNum
     * @return void
     */
    public function setEndIpNum(string $endIpNum): void;

    /**
     * @return string
     */
    public function getGeoipLocId(): string;

    /**
     * @param string $geoipLocId
     * @return void
     */
    public function setGeoipLocId(string $geoipLocId): void;

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string;

    /**
     * @param string|null $postalCode
     * @return void
     */
    public function setPostalCode(?string $postalCode): void;

    /**
     * @return string|null
     */
    public function getLatitude(): ?string;

    /**
     * @param string|null $latitude
     * @return void
     */
    public function setLatitude(?string $latitude): void;

    /**
     * @return string|null
     */
    public function getLongitude(): ?string;

    /**
     * @param string|null $longitude
     * @return void
     */
    public function setLongitude(?string $longitude): void;

    /**
     * @param array $keys
     * @return array
     */
    public function toArray(array $keys = []);
}
