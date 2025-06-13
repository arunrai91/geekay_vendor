<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\Admin;

use Amasty\AdminActionsLog\Model\ConfigProvider;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class IsLoggingAllowed
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Auth\Session
     */
    private $authSession;

    /**
     * @var bool|null
     */
    private $isLoggingEnabled = null;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        Auth\Session $authSession,
        ?State $appState = null,
        ?LoggerInterface $logger = null
    ) {
        $this->configProvider = $configProvider;
        $this->authSession = $authSession;
        $this->appState = $appState ?? ObjectManager::getInstance()->get(State::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    public function execute(): bool
    {
        try {
            if ($this->appState->getAreaCode() === Area::AREA_GLOBAL) {
                return true;
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
        if (!$this->authSession->isLoggedIn()) {
            return false;
        }

        if ($this->isLoggingEnabled === null) {
            $this->isLoggingEnabled = $this->configProvider->isEnabledLogAllAdmins()
                || in_array($this->authSession->getUser()->getId(), $this->configProvider->getAdminUsers());
        }

        return $this->isLoggingEnabled;
    }
}
