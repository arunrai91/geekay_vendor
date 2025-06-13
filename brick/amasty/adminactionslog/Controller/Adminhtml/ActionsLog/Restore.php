<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Controller\Adminhtml\ActionsLog;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Controller\Adminhtml\AbstractActionsLog;
use Amasty\AdminActionsLog\Restoring\RestoreProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Restore extends AbstractActionsLog
{
    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var RestoreProcessor
     */
    private $restoreProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        LogEntryRepositoryInterface $logEntryRepository,
        RestoreProcessor $restoreProcessor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logEntryRepository = $logEntryRepository;
        $this->restoreProcessor = $restoreProcessor;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            /** @var LogEntryInterface $logEntry */
            $logEntry = $this->logEntryRepository->getById((int)$this->getRequest()->getParam('id'));
            $this->restoreProcessor->restoreChanges($logEntry);
            $this->messageManager->addSuccessMessage(__('Changes have been successfully restored.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            $this->logger->critical($e);
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
