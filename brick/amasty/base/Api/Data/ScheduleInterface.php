<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Api\Data;

interface ScheduleInterface
{
    public const ID = 'id';
    public const CODE = 'code';
    public const LAST_SEND_DATE = 'last_send_date';
    public const TIME_INTERVALS = 'time_intervals';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param int $timestamp
     * @return void
     */
    public function setLastSendDate(int $timestamp): void;

    /**
     * @return int
     */
    public function getLastSendDate(): int;

    /**
     * @param string|null $intervals
     * @return void
     */
    public function setTimeIntervals(?string $intervals): void;

    /**
     * @return string|null
     */
    public function getTimeIntervals(): ?string;

    /**
     * @param array $keys
     * @return array
     */
    public function toArray(array $keys = []);
}
