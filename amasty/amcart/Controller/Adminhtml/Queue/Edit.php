<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml\Queue;

use Amasty\Acart\Api\Data\HistoryInterface;
use Amasty\Acart\Api\HistoryRepositoryInterface;
use Amasty\Acart\Controller\Adminhtml\Queue;
use Amasty\Acart\Model\Mail\TemplateBuilder;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Edit extends Queue
{
    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var TemplateBuilder
     */
    private $templateBuilder;

    public function __construct(
        Action\Context $context,
        HistoryRepositoryInterface $historyRepository,
        TemplateBuilder $templateBuilder
    ) {
        parent::__construct($context);
        $this->historyRepository = $historyRepository;
        $this->templateBuilder = $templateBuilder;
    }

    public function execute()
    {
        $historyId = (int)$this->getRequest()->getParam('id');
        try {
            $history = $this->historyRepository->getById($historyId);
        } catch (NotFoundException $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while editing the queue.'));

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
        $this->prepareEmailContent($history);

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Acart::acart_rule');
        $resultPage->setActiveMenu('Amasty_Acart::acart');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit queue item #%1', $history->getHistoryId()));

        return $resultPage;
    }

    private function prepareEmailContent(HistoryInterface $history): void
    {
        if (!$history->getEmailBody()) {
            $template = $this->templateBuilder->build($history);
            $emailBody = $template->processTemplate();
            //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $emailSubject = html_entity_decode((string)$template->getSubject(), ENT_QUOTES);
            $history->setEmailBody($emailBody);
            $history->setEmailSubject($emailSubject);
            $this->historyRepository->save($history);
        }
    }
}
