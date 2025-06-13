<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\ActiveSession;

use Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface;
use Magento\Framework\Model\AbstractModel;

class ActiveSession extends AbstractModel implements ActiveSessionInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const SESSION_ID = 'session_id';
    public const ADMIN_SESSION_INFO_ID = 'admin_session_info_id';
    public const USER_ID = 'user_id';
    public const USERNAME = 'username';
    public const FULL_NAME = 'full_name';
    public const IP = 'ip';
    public const SESSION_START = 'session_start';
    public const RECENT_ACTIVITY = 'recent_activity';
    public const LOCATION = 'location';
    public const COUNTRY_ID = 'country_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\ActiveSession::class);
        $this->setIdFieldName(self::ID);
    }

    public function getSessionId(): ?string
    {
        return $this->_getData(self::SESSION_ID);
    }

    public function setSessionId(string $sessionId): ActiveSessionInterface
    {
        return $this->setData(self::SESSION_ID, $sessionId);
    }

    public function getAdminSessionInfoId(): ?int
    {
        return $this->hasData(self::ADMIN_SESSION_INFO_ID)
            ? (int)$this->_getData(self::ADMIN_SESSION_INFO_ID)
            : null;
    }

    public function setAdminSessionInfoId(?int $adminSessionInfoId): ActiveSessionInterface
    {
        return $this->setData(self::ADMIN_SESSION_INFO_ID, $adminSessionInfoId);
    }

    public function getUserId(): ?int
    {
        return $this->hasData(self::USER_ID)
            ? (int)$this->_getData(self::USER_ID)
            : null;
    }

    public function setUserId(?int $userId): ActiveSessionInterface
    {
        return $this->setData(self::USER_ID, $userId);
    }

    public function getUsername(): ?string
    {
        return $this->_getData(self::USERNAME);
    }

    public function setUsername(string $username): ActiveSessionInterface
    {
        return $this->setData(self::USERNAME, $username);
    }

    public function getFullName(): ?string
    {
        return $this->_getData(self::FULL_NAME);
    }

    public function setFullName(string $fullName): ActiveSessionInterface
    {
        return $this->setData(self::FULL_NAME, $fullName);
    }

    public function getIp(): ?string
    {
        return $this->_getData(self::IP);
    }

    public function setIp(string $ip): ActiveSessionInterface
    {
        return $this->setData(self::IP, $ip);
    }

    public function getSessionStart(): ?string
    {
        return $this->_getData(self::SESSION_START);
    }

    public function setSessionStart(string $startTime): ActiveSessionInterface
    {
        return $this->setData(self::SESSION_START, $startTime);
    }

    public function getRecentActivity(): ?string
    {
        return $this->_getData(self::RECENT_ACTIVITY);
    }

    public function setRecentActivity(string $activityTime): ActiveSessionInterface
    {
        return $this->setData(self::RECENT_ACTIVITY, $activityTime);
    }

    public function getLocation(): ?string
    {
        return $this->_getData(self::LOCATION);
    }

    public function setLocation(string $location): ActiveSessionInterface
    {
        return $this->setData(self::LOCATION, $location);
    }

    public function getCountryId(): ?string
    {
        return $this->_getData(self::COUNTRY_ID);
    }

    public function setCountryId(string $countryId): ActiveSessionInterface
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }
}
