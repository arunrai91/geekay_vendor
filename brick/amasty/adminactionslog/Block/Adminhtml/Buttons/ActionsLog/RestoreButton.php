<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Block\Adminhtml\Buttons\ActionsLog;

use Amasty\AdminActionsLog\Api\LogEntryRepositoryInterface;
use Amasty\AdminActionsLog\Model\ConfigProvider;
use Amasty\AdminActionsLog\Restoring\RestoreValidator;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class RestoreButton implements ButtonProviderInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LogEntryRepositoryInterface
     */
    private $logEntryRepository;

    /**
     * @var RestoreValidator
     */
    private $restoreValidator;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        LogEntryRepositoryInterface $logEntryRepository,
        RestoreValidator $restoreValidator,
        Escaper $escaper
    ) {
        $this->configProvider = $configProvider;
        $this->logEntryRepository = $logEntryRepository;
        $this->restoreValidator = $restoreValidator;
        $this->escaper = $escaper;
        $this->request = $context->getRequest();
        $this->urlBuilder = $context->getUrlBuilder();
    }

    public function getButtonData()
    {
        $id = (int)$this->request->getParam('id');
        try {
            $logEntry = $this->logEntryRepository->getById($id);
        } catch (\Exception $e) {
            return [];
        }

        if ($this->restoreValidator->isValid($logEntry)) {
            return [
                'label' => __('Restore Changes'),
                'class' => 'restore_changes',
                'on_click' => $this->getClickAction($id),
                'sort_order' => 20
            ];
        }

        return [];
    }

    private function getClickAction(int $id): string
    {
        return sprintf(
            'confirmSetLocation("%s", "%s")',
            $this->escaper->escapeJs($this->configProvider->getRestoreSettingsText()),
            $this->urlBuilder->getUrl('amaudit/actionslog/restore', ['id' => $id])
        );
    }
}
