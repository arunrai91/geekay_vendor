<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Api\Data;

interface VisitHistoryEntryInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * @param string $username
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setUsername(string $username): VisitHistoryEntryInterface;

    /**
     * @return string|null
     */
    public function getFullName(): ?string;

    /**
     * @param string $fullName
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setFullName(string $fullName): VisitHistoryEntryInterface;

    /**
     * @return string|null
     */
    public function getSessionStart(): ?string;

    /**
     * @param string $sessionStart
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setSessionStart(string $sessionStart): VisitHistoryEntryInterface;

    /**
     * @return string|null
     */
    public function getSessionEnd(): ?string;

    /**
     * @param string $sessionEnd
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setSessionEnd(string $sessionEnd): VisitHistoryEntryInterface;

    /**
     * @return string|null
     */
    public function getIp(): ?string;

    /**
     * @param string $ip
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setIp(string $ip): VisitHistoryEntryInterface;

    /**
     * @return string|null
     */
    public function getLocation(): ?string;

    /**
     * @param string $location
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setLocation(string $location): VisitHistoryEntryInterface;

    /**
     * @return int
     */
    public function getSessionId(): ?string;

    /**
     * @param string $sessionId
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setSessionId(string $sessionId): VisitHistoryEntryInterface;

    /**
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryDetailInterface[]
     */
    public function getVisitHistoryDetails(): array;

    /**
     * @param array $visitHistoryDetails
     *
     * @return \Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface
     */
    public function setVisitHistoryDetails(array $visitHistoryDetails): VisitHistoryEntryInterface;
}
