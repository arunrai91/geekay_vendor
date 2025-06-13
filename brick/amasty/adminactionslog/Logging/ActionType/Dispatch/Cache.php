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

class Cache implements LoggingActionInterface
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
        $logEntry = $this->logEntryFactory->create([
            LogEntry\LogEntry::TYPE => LogEntryTypes::TYPE_CACHE,
            LogEntry\LogEntry::CATEGORY => __('Cache'),
            LogEntry\LogEntry::CATEGORY_NAME => __('Cache'),
            LogEntry\LogEntry::ITEM => $this->actionName2Label($this->metadata->getRequest()->getActionName()),
        ]);
        $this->logEntryRepository->save($logEntry);
    }

    private function actionName2Label(string $actionName): string
    {
        $actionLabels = [
            'cleanImages' => __('Flushed image cache'),
            'cleanMedia' => __('Flushed JavaScript/CSS cache'),
            'cleanStaticFiles' => __('Flushed static files cache'),
            'flushAll' => __('Flushed cache storage'),
            'flushSystem' => __('Flushed Magento cache storage'),
            'massDisable' => __('Cache disabled'),
            'massEnable' => __('Cache enabled'),
            'massRefresh' => __('Cache refreshed')
        ];

        return isset($actionLabels[$actionName]) ? $actionLabels[$actionName]->render() : $actionName;
    }
}
