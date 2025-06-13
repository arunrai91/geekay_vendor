<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\ActiveSession;

use Amasty\AdminActionsLog\Api\ActiveSessionRepositoryInterface;
use Amasty\AdminActionsLog\Api\Data\ActiveSessionInterface;
use Amasty\AdminActionsLog\Api\Data\ActiveSessionInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements ActiveSessionRepositoryInterface
{
    /**
     * @var ActiveSessionInterfaceFactory
     */
    private $activeSessionFactory;

    /**
     * @var ResourceModel\ActiveSession
     */
    private $resource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $activeSessions = [];

    public function __construct(
        ActiveSessionInterfaceFactory $activeSessionFactory,
        ResourceModel\ActiveSession $resource,
        ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->activeSessionFactory = $activeSessionFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getById(int $id): ActiveSessionInterface
    {
        if (!isset($this->activeSessions[$id])) {
            /** @var ActiveSessionInterface $activeSession */
            $activeSession = $this->activeSessionFactory->create();
            $this->resource->load($activeSession, $id);
            if (!$activeSession->getId()) {
                throw new NoSuchEntityException(__('Active Session with specified ID "%1" not found.', $id));
            }

            $this->activeSessions[$id] = $activeSession;
        }

        return $this->activeSessions[$id];
    }

    public function getBySessionId(string $sessionId): ActiveSessionInterface
    {
        /** @var ActiveSessionInterface $searchedRecord */
        $searchedRecord = $this->collectionFactory->create()
            ->addFieldToFilter(ActiveSession::SESSION_ID, $sessionId)
            ->getFirstItem();

        return $this->getById((int)$searchedRecord->getId());
    }

    public function save(ActiveSessionInterface $activeSession): ActiveSessionInterface
    {
        try {
            if ($activeSession->getId()) {
                $activeSession = $this->getById((int)$activeSession->getId())->addData($activeSession->getData());
            }
            $this->resource->save($activeSession);

            unset($this->activeSessions[$activeSession->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save the Active Session. Error: %1', $e->getMessage()));
        }

        return $activeSession;
    }

    public function delete(ActiveSessionInterface $activeSession): bool
    {
        try {
            $this->resource->delete($activeSession);
            unset($this->activeSessions[$activeSession->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to delete the Active Session. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}
