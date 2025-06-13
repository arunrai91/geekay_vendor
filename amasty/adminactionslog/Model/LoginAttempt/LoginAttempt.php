<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterface;
use Magento\Framework\Model\AbstractModel;

class LoginAttempt extends AbstractModel implements LoginAttemptInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const DATE = 'date';
    public const USERNAME = 'username';
    public const FULL_NAME = 'full_name';
    public const IP = 'ip';
    public const STATUS = 'status';
    public const LOCATION = 'location';
    public const COUNTRY_ID = 'country_id';
    public const USER_AGENT = 'user_agent';
    /**#@-*/

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\LoginAttempt::class);
        $this->setIdFieldName(self::ID);
    }

    public function getDate(): ?string
    {
        return $this->_getData(self::DATE);
    }

    public function setDate(string $date): LoginAttemptInterface
    {
        return $this->setData(self::DATE, $date);
    }

    public function getUsername(): ?string
    {
        return $this->_getData(self::USERNAME);
    }

    public function setUsername(string $username): LoginAttemptInterface
    {
        return $this->setData(self::USERNAME, $username);
    }

    public function getFullName(): ?string
    {
        return $this->_getData(self::FULL_NAME);
    }

    public function setFullName(string $fullName): LoginAttemptInterface
    {
        return $this->setData(self::FULL_NAME, $fullName);
    }

    public function getIp(): ?string
    {
        return $this->_getData(self::IP);
    }

    public function setIp(string $ip): LoginAttemptInterface
    {
        return $this->setData(self::IP, $ip);
    }

    public function getStatus(): ?int
    {
        return $this->hasData(self::STATUS) ? (int)$this->_getData(self::STATUS) : null;
    }

    public function setStatus(int $status): LoginAttemptInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getLocation(): ?string
    {
        return $this->_getData(self::LOCATION);
    }

    public function setLocation(string $location): LoginAttemptInterface
    {
        return $this->setData(self::LOCATION, $location);
    }

    public function getCountryId(): ?string
    {
        return $this->_getData(self::COUNTRY_ID);
    }

    public function setCountryId(string $countryId): LoginAttemptInterface
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    public function getUserAgent(): ?string
    {
        return $this->_getData(self::USER_AGENT);
    }

    public function setUserAgent(string $userAgent): LoginAttemptInterface
    {
        return $this->setData(self::USER_AGENT, $userAgent);
    }
}
