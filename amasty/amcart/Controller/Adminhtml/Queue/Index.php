<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Abandoned Cart Email Base for Magento 2
 */

namespace Amasty\Acart\Controller\Adminhtml\Queue;

use Amasty\Acart\Controller\Adminhtml\Queue;
use Amasty\Base\Model\ModuleInfoProvider;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Factory as MessageFactory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Phrase;

class Index extends Queue
{
    public const CRON_FAQ_LINK = 'https://amasty.com/knowledge-base/topic-magento-related-questions.html'
        . '?utm_source=extension&utm_medium=link&utm_campaign=abandoned-cart-m2-emails-queue-cron-faq#97';

    public const CRON_FAQ_LINK_MARKETPLACE = 'https://amasty.com/docs/doku.php?id=magento_2:abandoned-cart-email'
        . '&utm_source=extension&utm_medium=link&utm_campaign=acart_m2_guide#cron_tasks_list';

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        Action\Context $context,
        MessageFactory $messageFactory,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        parent::__construct($context);
        $this->messageFactory = $messageFactory;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    public function execute()
    {
        $this->addCronFaqMessage();
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Acart::acart_queue');
        $resultPage->addBreadcrumb(__('Marketing'), __('Marketing'));
        $resultPage->getConfig()->getTitle()->prepend(__('Queue'));

        return $resultPage;
    }

    private function addCronFaqMessage(): void
    {
        $faqLink = self::CRON_FAQ_LINK;
        if ($this->moduleInfoProvider->isOriginMarketplace()) {
            $faqLink = self::CRON_FAQ_LINK_MARKETPLACE;
        }

        $message = __('If there are no emails in the queue for a long time, please make sure that cron is '
            . 'properly configured for your Magento. Please find more information '
            . '<a class="new-page-url" href=\'%1\' target=\'_blank\'>here</a>.', $faqLink);

        $this->messageManager->addMessage(
            $this->messageFactory->create(MessageInterface::TYPE_WARNING, $message)
        );
    }
}
