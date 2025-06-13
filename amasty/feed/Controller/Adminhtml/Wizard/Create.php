<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Controller\Adminhtml\Wizard;

use Amasty\Feed\Api\Data\FeedInterfaceFactory;
use Amasty\Feed\Api\FeedRepositoryInterface as FeedRepository;
use Amasty\Feed\Api\FeedTemplateRepositoryInterface as TemplateRepository;
use Amasty\Feed\Model\Feed\Converter;
use Amasty\Feed\Model\FeedTemplate;
use Amasty\Feed\Ui\Component\Form\Wizard\TemplateOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Create extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Amasty_Feed::feed';

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var TemplateRepository
     */
    private $templateRepository;

    /**
     * @var FeedRepository
     */
    private $feedRepository;

    /**
     * @var FeedInterfaceFactory
     */
    private $feedFactory;

    public function __construct(
        Context $context,
        Converter $converter,
        TemplateRepository $templateRepository,
        FeedRepository $feedRepository,
        FeedInterfaceFactory $feedFactory
    ) {
        $this->converter = $converter;
        $this->templateRepository = $templateRepository;
        $this->feedRepository = $feedRepository;
        $this->feedFactory = $feedFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $generalParams = $this->getRequest()->getParam('general');
        $templateCode = $generalParams['template_code'] ?? '';

        if (!$templateCode) {
            $this->messageManager->addErrorMessage(__('Please select template'));

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        if ($templateCode === TemplateOptions::CUSTOM_FEED_CODE) {
            return $this->resultRedirectFactory->create()->setPath('amfeed/feed/newaction');
        }

        try {
            $template = $this->templateRepository->getBy($templateCode, FeedTemplate::TEMPLATE_CODE);
        } catch (NoSuchEntityException $exception) {
            $this->getMessageManager()->addErrorMessage($exception->getMessage());

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        try {
            $feed = $this->feedFactory->create();
            $feedModel = $this->converter->convertTemplateToFeed($template, $feed);
            $this->feedRepository->save($feedModel, true);

            return $this->resultRedirectFactory->create()->setPath(
                'amfeed/feed/edit',
                ['id' => $feedModel->getEntityId()]
            );
        } catch (CouldNotSaveException $exception) {
            $this->getMessageManager()->addErrorMessage($exception->getMessage());

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
    }
}
