<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActionsLog;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterfaceFactory;
use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActionsLog;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\Grid\CollectionFactory;
use Amasty\AdminActionsLog\Restoring\RestoreProcessor;
use Amasty\AdminActionsLog\Restoring\RestoreValidator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassRestore extends AbstractActionsLog
{
    /**
     * @var RestoreProcessor
     */
    private $restoreProcessor;

    /**
     * @var RestoreValidator
     */
    private $restoreValidator;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LogEntryInterfaceFactory
     */
    private $logEntryFactory;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $entryRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        RestoreProcessor $restoreProcessor,
        RestoreValidator $restoreValidator,
        CollectionFactory $collectionFactory,
        LogEntryInterfaceFactory $logEntryFactory,
        LogEntryRepositoryInterface $entryRepository,
        Filter $filter,
        Batch $batch,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->restoreProcessor = $restoreProcessor;
        $this->restoreValidator = $restoreValidator;
        $this->filter = $filter;
        $this->batch = $batch;
        $this->collectionFactory = $collectionFactory;
        $this->logEntryFactory = $logEntryFactory;
        $this->entryRepository = $entryRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $successRestore = $skippedRestore = $failedRestore = [];

        /** @var \Magento\Framework\Api\Search\DocumentInterface $logEntryDocument */
        foreach ($this->batch->getItems($collection, 10) as $batch) {
            foreach ($batch as $logEntryDocument) {
                $logEntry = $this->entryRepository->getById((int)$logEntryDocument->getId());

                if ($this->restoreValidator->isValid($logEntry)) {
                    try {
                        $this->restoreProcessor->restoreChanges($logEntry);
                        $successRestore[] = $logEntry->getId();
                    } catch (LocalizedException $e) {
                        $failedRestore[] = $logEntry->getId();
                    } catch (\Exception $e) {
                        $failedRestore[] = $logEntry->getId();
                        $this->logger->critical($e);
                    }
                } else {
                    $skippedRestore[] = $logEntry->getId();
                }
            }
        }

        if (!empty($successRestore)) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been restored.', count($successRestore))
            );
        }
        if (!empty($skippedRestore)) {
            $this->messageManager->addWarningMessage(
                __('The restoring of records (%1) is not allowed.', implode(', ', $skippedRestore))
            );
        }
        if (!empty($failedRestore)) {
            $this->messageManager->addErrorMessage(
                __('Failed to restore the records (%1).', implode(', ', $failedRestore))
            );
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
