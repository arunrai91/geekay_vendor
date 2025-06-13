<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

interface ActiveSessionInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getSessionId(): ?string;

    /**
     * @param string $sessionId
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setSessionId(string $sessionId): ActiveSessionInterface;

    /**
     * @return int|null
     */
    public function getAdminSessionInfoId(): ?int;

    /**
     * @param int|null $adminSessionInfoId
     * @return ActiveSessionInterface
     */
    public function setAdminSessionInfoId(?int $adminSessionInfoId): ActiveSessionInterface;

    /**
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * @param int|null $userId
     * @return ActiveSessionInterface
     */
    public function setUserId(?int $userId): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * @param string $username
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setUsername(string $username): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getFullName(): ?string;

    /**
     * @param string $fullName
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setFullName(string $fullName): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getIp(): ?string;

    /**
     * @param string $ip
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setIp(string $ip): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getSessionStart(): ?string;

    /**
     * @param string $startTime
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setSessionStart(string $startTime): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getRecentActivity(): ?string;

    /**
     * @param string $activityTime
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setRecentActivity(string $activityTime): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getLocation(): ?string;

    /**
     * @param string $location
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setLocation(string $location): ActiveSessionInterface;

    /**
     * @return string|null
     */
    public function getCountryId(): ?string;

    /**
     * @param string $countryId
     * @return \Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface
     */
    public function setCountryId(string $countryId): ActiveSessionInterface;
}
