<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Delete;

use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandlerProvider;
use Amasty\AdminActionsLog\Model\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class Entity implements LoggingActionInterface
{
    public const DELETE_LOG_ENTRY_POSTFIX = '.delete_log_entry';

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var SaveHandlerProvider
     */
    private $entityTypeProvider;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var LogEntry\AdminLogEntryFactory
     */
    private $logEntryFactory;

    public function __construct(
        MetadataInterface $metadata,
        SaveHandlerProvider $entityTypeProvider,
        ObjectDataStorageInterface $dataStorage,
        LogEntry\AdminLogEntryFactory $logEntryFactory
    ) {
        $this->metadata = $metadata;
        $this->entityTypeProvider = $entityTypeProvider;
        $this->dataStorage = $dataStorage;
        $this->logEntryFactory = $logEntryFactory;
    }

    public function execute(): void
    {
        if ($deletedObject = $this->metadata->getObject()) {
            $entityLogType = $this->entityTypeProvider->get(get_class($deletedObject));
            $category = sprintf(
                '%s/%s',
                $this->metadata->getRequest()->getModuleName(),
                $this->metadata->getRequest()->getControllerName()
            );

            $lodEntryData = array_merge(
                [LogEntry\LogEntry::CATEGORY_NAME => $category],
                $entityLogType->getLogMetadata($this->metadata),
                [
                    LogEntry\LogEntry::CATEGORY => $category,
                    LogEntry\LogEntry::TYPE => LogEntryTypes::TYPE_DELETE,
                    LogEntry\LogEntry::STORE_ID => 0
                ]
            );
            $logEntry = $this->logEntryFactory->create($lodEntryData);
            $storageKey = spl_object_id($deletedObject) . self::DELETE_LOG_ENTRY_POSTFIX;
            $this->dataStorage->set($storageKey, ['log' => $logEntry]);
        }
    }
}
