<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Plugin\Security\Model\AdminSessionsManager;

use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\AdminSessionsManager;
use Psr\Log\LoggerInterface;

class UpdateAdminSessionInfoId
{
    /**
     * @var ActiveSessionResource
     */
    private $activeSessionResource;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Flag to mark when sessions validation has been passed
     * to prevent unnecessary queries.
     *
     * @var bool
     */
    private $isValid = false;

    public function __construct(
        ActiveSessionResource $activeSessionResource,
        Session $authSession,
        LoggerInterface $logger
    ) {
        $this->activeSessionResource = $activeSessionResource;
        $this->authSession = $authSession;
        $this->logger = $logger;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentSession(
        AdminSessionsManager $subject,
        AdminSessionInfo $result
    ): AdminSessionInfo {
        if (!$this->isValid) {
            $adminSessionInfoId = (int)$result->getId();
            if (!empty($this->authSession->getSessionId())
                && $adminSessionInfoId
                && ($user = $this->authSession->getUser())
            ) {
                $userId = (int)$user->getId();
                try {
                    $this->activeSessionResource->updateIfSessionInvalid($userId, $adminSessionInfoId);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
            $this->isValid = true;
        }

        return $result;
    }
}
