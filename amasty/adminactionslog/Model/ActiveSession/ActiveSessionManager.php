<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\ActiveSession;

use Amasty\AdminActionsLog\Api\ActiveSessionManagerInterface;
use Amasty\AdminActionsLog\Api\ActiveSessionRepositoryInterface;
use Amasty\AdminActionsLog\Api\Data\ActiveSessionInterfaceFactory;
use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\CollectionFactory as ActiveSessionCollectionFactory;
use Amasty\AdminActionsLog\Model\Admin\SessionUserDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\ResourceModel\AdminSessionInfo as AdminSessionInfoResource;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;

class ActiveSessionManager implements ActiveSessionManagerInterface
{
    public const SESSION_LIFETIME_CONFIG_PATH = 'admin/security/session_lifetime';

    /**
     * @var SessionUserDataProvider
     */
    private $sessionUserDataProvider;

    /**
     * @var ActiveSessionRepositoryInterface
     */
    private $activeSessionRepository;

    /**
     * @var ActiveSessionInterfaceFactory
     */
    private $activeSessionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CollectionFactory
     */
    private $adminSessionInfoCollectionFactory;

    /**
     * @var AdminSessionInfoResource
     */
    private $adminSessionInfoResource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ActiveSessionCollectionFactory
     */
    private $activeSessionCollectionFactory;

    /**
     * @var ActiveSessionResource
     */
    private $activeSession;

    public function __construct(
        SessionUserDataProvider $sessionUserDataProvider,
        ActiveSessionRepositoryInterface $activeSessionRepository,
        ActiveSessionInterfaceFactory $activeSessionFactory,
        DateTime $dateTime,
        CollectionFactory $adminSessionInfoCollectionFactory,
        AdminSessionInfoResource $adminSessionInfoResource,
        ScopeConfigInterface $scopeConfig,
        ActiveSessionCollectionFactory $activeSessionCollectionFactory,
        ActiveSessionResource $activeSession = null
    ) {
        $this->sessionUserDataProvider = $sessionUserDataProvider;
        $this->activeSessionRepository = $activeSessionRepository;
        $this->activeSessionFactory = $activeSessionFactory;
        $this->dateTime = $dateTime;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
        $this->adminSessionInfoResource = $adminSessionInfoResource;
        $this->scopeConfig = $scopeConfig;
        $this->activeSessionCollectionFactory = $activeSessionCollectionFactory;
        // OM for backward compatibility
        $this->activeSession = $activeSession ?? ObjectManager::getInstance()->get(ActiveSessionResource::class);
    }

    public function initNew(): void
    {
        $userData = $this->sessionUserDataProvider->getUserPreparedData();
        $activeSessionModel = $this->activeSessionFactory->create()->setData($userData);

        $this->activeSessionRepository->save($activeSessionModel);
    }

    public function update(): void
    {
        $sessionId = $this->sessionUserDataProvider->getSessionId();
        try {
            $activeSessionModel = $this->activeSessionRepository->getBySessionId($sessionId);
            $activeSessionModel->setRecentActivity($this->dateTime->date());

            $this->activeSessionRepository->save($activeSessionModel);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }

    public function terminate(string $sessionId = null): void
    {
        if ($sessionId === null) {
            $sessionId = $this->sessionUserDataProvider->getSessionId();
        }

        try {
            $activeSessionModel = $this->activeSessionRepository->getBySessionId($sessionId);
            $this->activeSessionRepository->delete($activeSessionModel);
        } catch (NoSuchEntityException $e) {
            return;
        }

        $adminSessionInfoId = (int)$activeSessionModel->getAdminSessionInfoId();
        if ($adminSessionInfoId) {
            // Since Magento 2.4.3-p1, 2.3.7-p2 session_id is no longer used in admin_user_session
            $this->destroySessionByAdminSessionInfoId($adminSessionInfoId);
        } else {
            $this->destroySessionBySessionId($sessionId);
        }
    }

    public function getInactiveSessions(): array
    {
        $sessionLifeTime =  $this->scopeConfig->getValue(self::SESSION_LIFETIME_CONFIG_PATH);
        if (empty($sessionLifeTime)) {
            $sessionLifeTime = 900;
        }
        $activeSessionCollection = $this->activeSessionCollectionFactory->create();
        $activeSessionCollection->addFieldToFilter(
            ActiveSession::RECENT_ACTIVITY,
            ['lteq' => $this->dateTime->gmtDate('Y-m-d H:i:s', "- $sessionLifeTime seconds") ]
        );

        return $activeSessionCollection->getColumnValues(ActiveSession::SESSION_ID);
    }

    public function terminateAll(): void
    {
        $sessionIds = $this->activeSession->getAllSessionIds();
        foreach ($sessionIds as $sessionId) {
            $this->terminate($sessionId);
        }
    }

    private function destroySessionBySessionId(string $sessionId): void
    {
        /** @var AdminSessionInfo $adminSessionInfo */
        $adminSessionInfo = $this->adminSessionInfoCollectionFactory->create()
            ->addFieldToFilter('session_id', $sessionId)
            ->getFirstItem();
        if (!$adminSessionInfo->getId()) {
            return;
        }

        $this->updateAdminSessionInfoStatus($adminSessionInfo);
    }

    private function destroySessionByAdminSessionInfoId(int $adminSessionInfoId): void
    {
        /** @var AdminSessionInfo $adminSessionInfo */
        $adminSessionInfo = $this->adminSessionInfoCollectionFactory->create()
            ->addFieldToFilter('id', $adminSessionInfoId)
            ->getFirstItem();
        if (!$adminSessionInfo->getId()) {
            return;
        }

        $this->updateAdminSessionInfoStatus($adminSessionInfo);
    }

    private function updateAdminSessionInfoStatus(AdminSessionInfo $adminSessionInfo): void
    {
        $this->adminSessionInfoResource->updateStatusByUserId(
            AdminSessionInfo::LOGGED_OUT_MANUALLY,
            $adminSessionInfo->getUserId(),
            [AdminSessionInfo::LOGGED_IN]
        );
    }
}
