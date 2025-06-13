<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Plugin\Security\Model\AdminSessionsManager;

use Amasty\AdminActionsLog\Api\ActiveSessionRepositoryInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\NoSuchEntityException;

class AddAdminSessionInfoId
{
    /**
     * @var ActiveSessionRepositoryInterface
     */
    private $activeSessionRepository;

    /**
     * @var Session
     */
    private $authSession;

    public function __construct(
        ActiveSessionRepositoryInterface $activeSessionRepository,
        Session $authSession
    ) {
        $this->activeSessionRepository = $activeSessionRepository;
        $this->authSession = $authSession;
    }

    public function afterProcessLogin(): void
    {
        $sessionId = $this->authSession->getSessionId();
        $adminSessionInfoId = $this->authSession->getAdminSessionInfoId();

        if (!empty($sessionId) && $adminSessionInfoId) {
            try {
                $activeSessionModel = $this->activeSessionRepository->getBySessionId($sessionId);
            } catch (NoSuchEntityException $exception) {
                return;
            }

            $activeSessionModel->setAdminSessionInfoId((int)$adminSessionInfoId);
            $this->activeSessionRepository->save($activeSessionModel);
        }
    }
}
