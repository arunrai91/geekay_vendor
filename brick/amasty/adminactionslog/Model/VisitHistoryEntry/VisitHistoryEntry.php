<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry;

use Amasty\AdminActionsLog\Api\Data\VisitHistoryEntryInterface;
use Magento\Framework\Model\AbstractModel;

class VisitHistoryEntry extends AbstractModel implements VisitHistoryEntryInterface
{
    public const ID = 'id';
    public const USERNAME = 'username';
    public const FULL_NAME = 'full_name';
    public const SESSION_START = 'session_start';
    public const SESSION_END = 'session_end';
    public const IP = 'ip';
    public const LOCATION = 'location';
    public const SESSION_ID = 'session_id';
    public const VISIT_HISTORY_DETAILS = 'visit_history_details';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\VisitHistoryEntry::class);
        $this->setIdFieldName(self::ID);
    }

    public function getUsername(): ?string
    {
        return $this->_getData(self::USERNAME);
    }

    public function setUsername(string $username): VisitHistoryEntryInterface
    {
        $this->setData(self::USERNAME, $username);

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->_getData(self::FULL_NAME);
    }

    public function setFullName(string $fullName): VisitHistoryEntryInterface
    {
        return $this->setData(self::FULL_NAME, $fullName);
    }

    public function getSessionStart(): ?string
    {
        return $this->_getData(self::SESSION_START);
    }

    public function setSessionStart(string $sessionStart): VisitHistoryEntryInterface
    {
        $this->setData(self::SESSION_START, $sessionStart);

        return $this;
    }

    public function getSessionEnd(): ?string
    {
        return $this->_getData(self::SESSION_END);
    }

    public function setSessionEnd(string $sessionEnd): VisitHistoryEntryInterface
    {
        $this->setData(self::SESSION_END, $sessionEnd);

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->_getData(self::IP);
    }

    public function setIp(string $ip): VisitHistoryEntryInterface
    {
        $this->setData(self::IP, $ip);

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->_getData(self::LOCATION);
    }

    public function setLocation(string $location): VisitHistoryEntryInterface
    {
        $this->setData(self::LOCATION, $location);

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->_getData(self::SESSION_ID);
    }

    public function setSessionId(string $sessionId): VisitHistoryEntryInterface
    {
        $this->setData(self::SESSION_ID, $sessionId);

        return $this;
    }

    public function getVisitHistoryDetails(): array
    {
        return (array)$this->_getData(self::VISIT_HISTORY_DETAILS);
    }

    public function setVisitHistoryDetails(array $visitHistoryDetails): VisitHistoryEntryInterface
    {
        $this->setData(self::VISIT_HISTORY_DETAILS, $visitHistoryDetails);

        return $this;
    }
}
