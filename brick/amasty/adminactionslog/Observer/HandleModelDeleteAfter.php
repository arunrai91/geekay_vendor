<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Observer;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\ActionType\Delete\Entity;
use Magento\Framework\Event\ObserverInterface;

class HandleModelDeleteAfter implements ObserverInterface
{
    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    public function __construct(
        ObjectDataStorageInterface $dataStorage,
        LogEntryRepositoryInterface $logEntryRepository
    ) {
        $this->dataStorage = $dataStorage;
        $this->logEntryRepository = $logEntryRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $deletedObject = $observer->getObject();
        $storageKey = $deletedObject ? spl_object_id($deletedObject) . Entity::DELETE_LOG_ENTRY_POSTFIX : '';

        if ($deletedObject && $this->dataStorage->isExists($storageKey)) {
            $logEntry = $this->dataStorage->get($storageKey)['log'] ?? null;

            if ($logEntry) {
                $this->logEntryRepository->save($logEntry);
            }
        }
    }
}
