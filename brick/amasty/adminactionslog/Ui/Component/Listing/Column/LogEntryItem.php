<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Amasty\AdminActionsLog\Api\Data\LogEntryInterface;
use Amasty\AdminActionsLog\Api\Data\LogEntryInterfaceFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class LogEntryItem extends Column
{
    /**
     * @var LogEntryInterfaceFactory
     */
    private $logEntryFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LogEntryInterfaceFactory $logEntryFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->logEntryFactory = $logEntryFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['item'])) {
                $logEntry = $this->logEntryFactory->create(['data' => $item]);
                $item['view_url'] = $this->buildViewUrl($logEntry);
            }
        }

        return $dataSource;
    }

    private function buildViewUrl(LogEntryInterface $logEntry): ?string
    {
        $category = (string)$logEntry->getCategory();
        $categoryActionParts = explode('/', $category);

        if (!$logEntry->getElementId() || count($categoryActionParts) < 3) {
            return null;
        }

        return $this->urlBuilder->getUrl(
            $category,
            [
                $logEntry->getParameterName() => $logEntry->getViewElementId() ?: $logEntry->getElementId()
            ]
        );
    }
}
