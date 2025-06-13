<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\AfterSave;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Api\Logging\ObjectDataStorageInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandlerProvider;
use Amasty\AdminActionsLog\Logging\Util\DetailsBuilder;
use Amasty\AdminActionsLog\Model\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Amasty\AdminActionsLog\Restoring\Entity\RestoreHandler\AbstractHandler;

class Entity implements LoggingActionInterface
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var DetailsBuilder
     */
    private $detailsBuilder;

    /**
     * @var SaveHandlerProvider
     */
    private $entityTypeProvider;

    /**
     * @var ObjectDataStorageInterface
     */
    private $dataStorage;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var LogEntry\AdminLogEntryFactory
     */
    private $logEntryFactory;

    public function __construct(
        MetadataInterface $metadata,
        DetailsBuilder $detailsBuilder,
        SaveHandlerProvider $entityTypeProvider,
        ObjectDataStorageInterface $dataStorage,
        LogEntryRepositoryInterface $logEntryRepository,
        LogEntry\AdminLogEntryFactory $logEntryFactory
    ) {
        $this->metadata = $metadata;
        $this->detailsBuilder = $detailsBuilder;
        $this->entityTypeProvider = $entityTypeProvider;
        $this->dataStorage = $dataStorage;
        $this->logEntryRepository = $logEntryRepository;
        $this->logEntryFactory = $logEntryFactory;
    }

    public function execute(): void
    {
        if ($loggingObject = $this->metadata->getObject()) {
            // storageEntityKey could be set while executing entities by schedule.
            $storageKey = $this->metadata->getStorageEntityKey() ?? spl_object_id($loggingObject) . '.before';
            $entityLogType = $this->entityTypeProvider->get(get_class($loggingObject));
            $beforeData = $this->dataStorage->get($storageKey) ?? [];
            $afterData = $entityLogType->processAfterSave($loggingObject);
            $detailsList = $this->detailsBuilder->build(get_class($loggingObject), $beforeData, $afterData);

            if (!empty($detailsList)) {
                $logEntry = $this->logEntryFactory->create($this->prepareLogEntryData($loggingObject));
                $logEntry->setLogDetails($detailsList);
                $this->logEntryRepository->save($logEntry);
            }
        }
    }

    private function prepareLogEntryData($object): array
    {
        $entityLogType = $this->entityTypeProvider->get(get_class($object));
        $defaultCategory = sprintf(
            '%s/%s',
            $this->metadata->getRequest()->getModuleName(),
            $this->metadata->getRequest()->getControllerName()
        );

        switch (true) {
            case (bool)$object->isObjectNew():
                $actionType = LogEntryTypes::TYPE_NEW;
                break;
            default:
                $actionType = LogEntryTypes::TYPE_EDIT;
        }

        $logEntryData = array_merge(
            [
                LogEntry\LogEntry::TYPE => $actionType,
                LogEntry\LogEntry::CATEGORY => $defaultCategory,
                LogEntry\LogEntry::CATEGORY_NAME => $defaultCategory,
            ],
            $entityLogType->getLogMetadata($this->metadata)
        );

        $storageKey = spl_object_id($object) . '.' . AbstractHandler::STORAGE_CODE_PREFIX;
        if ($this->dataStorage->isExists($storageKey)) {
            $logEntryData[LogEntry\LogEntry::TYPE] = LogEntryTypes::TYPE_RESTORE;
        }

        return $logEntryData;
    }
}
