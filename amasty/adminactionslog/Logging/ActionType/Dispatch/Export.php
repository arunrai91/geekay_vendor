<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\ActionType\Dispatch;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Api\Logging\LoggingActionInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;

class Export implements LoggingActionInterface
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

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
        LogEntryRepositoryInterface $logEntryRepository,
        LogEntry\AdminLogEntryFactory $logEntryFactory
    ) {
        $this->metadata = $metadata;
        $this->logEntryRepository = $logEntryRepository;
        $this->logEntryFactory = $logEntryFactory;
    }

    public function execute(): void
    {
        $category = sprintf(
            '%s %s',
            $this->metadata->getRequest()->getModuleName(),
            $this->metadata->getRequest()->getControllerName()
        );
        $logEntry = $this->logEntryFactory->create([
            LogEntry\LogEntry::TYPE => LogEntryTypes::TYPE_EXPORT,
            LogEntry\LogEntry::CATEGORY => $category,
            LogEntry\LogEntry::CATEGORY_NAME => $category,
            LogEntry\LogEntry::ITEM => __('Data was exported.'),
        ]);
        $this->logEntryRepository->save($logEntry);
    }
}
