<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GeoIP Data for Magento 2 (System)
 */

namespace Amasty\Geoip\Api\Data;

interface LocationInterface
{
    public const LOCATION_ID = 'location_id';
    public const GEOIP_LOC_ID = 'geoip_loc_id';
    public const COUNTRY = 'country';
    public const CITY = 'city';
    public const REGION = 'region';

    /**
     * @return int|null
     */
    public function getId(): ?int;

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
    public function getCountry(): ?string;

    /**
     * @param string|null $country
     * @return void
     */
    public function setCountry(?string $country): void;

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @param string|null $city
     * @return void
     */
    public function setCity(?string $city): void;

    /**
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * @param string $region
     * @return void
     */
    public function setRegion(string $region): void;

    /**
     * @param array $keys
     * @return array
     */
    public function toArray(array $keys = []);
}
