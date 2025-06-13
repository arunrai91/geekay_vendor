<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\LoginAttempt;

use Amasty\AdminActionsLog\Api\Data\LoginAttemptInterfaceFactory;
use Amasty\AdminActionsLog\Api\LoginAttemptManagerInterface;
use Amasty\AdminActionsLog\Api\LoginAttemptRepositoryInterface;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Amasty\AdminActionsLog\Model\LoginAttempt\Notification\Processor;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\User\Api\Data\UserInterfaceFactory;

class LoginAttemptManager implements LoginAttemptManagerInterface
{
    /**
     * @var SessionUserDataProvider
     */
    private $sessionUserDataProvider;

    /**
     * @var LoginAttemptInterfaceFactory
     */
    private $loginAttemptFactory;

    /**
     * @var LoginAttemptRepositoryInterface
     */
    private $loginAttemptRepository;

    /**
     * @var UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var Processor
     */
    private $notificationProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        SessionUserDataProvider $sessionUserDataProvider,
        LoginAttemptInterfaceFactory $loginAttemptFactory,
        LoginAttemptRepositoryInterface $loginAttemptRepository,
        UserInterfaceFactory $userFactory,
        Processor $notificationProcessor,
        ConfigProvider $configProvider,
        DateTime $dateTime
    ) {
        $this->sessionUserDataProvider = $sessionUserDataProvider;
        $this->loginAttemptFactory = $loginAttemptFactory;
        $this->loginAttemptRepository = $loginAttemptRepository;
        $this->userFactory = $userFactory;
        $this->notificationProcessor = $notificationProcessor;
        $this->configProvider = $configProvider;
        $this->dateTime = $dateTime;
    }

    public function saveAttempt(?string $username, int $status): void
    {
        $userData = $this->sessionUserDataProvider->getUserPreparedData();
        $loginAttempt = $this->loginAttemptFactory->create(['data' => $userData]);
        $loginAttempt->setDate($this->dateTime->date());
        $loginAttempt->setStatus($status);
        $loginAttempt->setUserAgent($this->sessionUserDataProvider->getUserAgent());

        if ($username && !$this->sessionUserDataProvider->getUserName()) {
            $loginAttempt->setUsername($username)->setFullName($username);
            $user = $this->userFactory->create()->loadByUsername($username);
            if ($user->getId()) {
                $loginAttempt->setUsername($user->getUserName());
                $loginAttempt->setFullName($user->getFirstName() . ' ' . $user->getLastName());
            }
        }

        $this->loginAttemptRepository->save($loginAttempt);
        $this->notificationProcessor->execute($loginAttempt);
    }

    public function clear(int $period = null): void
    {
        $this->loginAttemptRepository->clean($period);
    }
}
