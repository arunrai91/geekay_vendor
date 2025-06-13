<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\Admin;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\Geoip\Model\Geolocation;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Magento\Backend\Model\Auth;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\HTTP;
use Magento\Framework\Locale\ListsInterface;

class SessionUserDataProvider
{
    /**
     * @var HTTP\Header
     */
    private $header;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var Auth\Session
     */
    private $authSession;

    /**
     * @var ListsInterface
     */
    private $locateList;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var GetCustomerIp
     */
    private $getCustomerIp;

    public function __construct(
        HTTP\Header $header,
        Geolocation $geolocation,
        Auth\Session $authSession,
        ListsInterface $locateList,
        ConfigProvider $configProvider,
        DataObjectFactory $dataObjectFactory,
        GetCustomerIp $getCustomerIp
    ) {
        $this->header = $header;
        $this->geolocation = $geolocation;
        $this->authSession = $authSession;
        $this->locateList = $locateList;
        $this->configProvider = $configProvider;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->getCustomerIp = $getCustomerIp;
    }

    public function getIpAddress(): ?string
    {
        return $this->getCustomerIp->getCurrentIp() ?: null;
    }

    public function getSessionId(): string
    {
        return $this->authSession->getSessionId();
    }

    public function getUserAgent(): string
    {
        return $this->header->getHttpUserAgent();
    }

    public function getFullUserName(): ?string
    {
        $user = $this->authSession->getUser();

        return $user !== null
            ? $user->getFirstName() . ' ' . $user->getLastName()
            : null;
    }

    public function getUserName(): ?string
    {
        return $this->authSession->getUser()
            ? $this->authSession->getUser()->getUserName()
            : null;
    }

    public function getUserId(): ?int
    {
        return $this->authSession->getUser()
            ? (int)$this->authSession->getUser()->getId()
            : null;
    }

    public function getLocation(): DataObject
    {
        $location = $this->dataObjectFactory->create();

        if ($this->configProvider->isEnabledGeolocation()) {
            $locationData = $this->geolocation->locate($this->getIpAddress());
            $location->setData([
                'country' => $this->locateList->getCountryTranslation($locationData->getCountry()),
                'city' => $this->locateList->getCountryTranslation($locationData->getCity())
            ]);
        }

        return $location;
    }

    public function getUserPreparedData(): array
    {
        $location = $this->getLocation();

        return [
            'ip' => $this->getIpAddress(),
            'user_id' => $this->getUserId(),
            'username' => $this->getUserName(),
            'full_name' => $this->getFullUserName(),
            'location' => sprintf('%s %s', $location->getCountry(), $location->getCity()),
            'country_id' => $location->getCountry(),
            'session_id' => $this->getSessionId()
        ];
    }
}
