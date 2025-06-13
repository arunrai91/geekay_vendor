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

    public function __construct(
        ConfigProvider $configProvider,
        Auth\Session $authSession
    ) {
        $this->configProvider = $configProvider;
        $this->authSession = $authSession;
    }

    public function execute(): bool
    {
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
