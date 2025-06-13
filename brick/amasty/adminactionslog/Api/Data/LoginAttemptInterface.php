<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

interface LoginAttemptInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param string $date
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setDate(string $date): LoginAttemptInterface;

    /**
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * @param string $username
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setUsername(string $username): LoginAttemptInterface;

    /**
     * @return string|null
     */
    public function getFullName(): ?string;

    /**
     * @param string $fullName
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setFullName(string $fullName): LoginAttemptInterface;

    /**
     * @return string|null
     */
    public function getIp(): ?string;

    /**
     * @param string $ip
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setIp(string $ip): LoginAttemptInterface;

    /**
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * @param int $status
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setStatus(int $status): LoginAttemptInterface;

    /**
     * @return string|null
     */
    public function getLocation(): ?string;

    /**
     * @param string $location
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setLocation(string $location): LoginAttemptInterface;

    /**
     * @return string|null
     */
    public function getCountryId(): ?string;

    /**
     * @param string $countryId
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setCountryId(string $countryId): LoginAttemptInterface;

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string;

    /**
     * @param string $userAgent
     * @return \Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface
     */
    public function setUserAgent(string $userAgent): LoginAttemptInterface;
}
