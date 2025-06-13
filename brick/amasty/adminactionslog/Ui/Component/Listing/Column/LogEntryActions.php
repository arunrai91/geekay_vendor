<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class LogEntryActions extends Column
{
    /**
     * @var LogEntryTypes
     */
    private $logEntryTypes;

    /**
     * @var array
     */
    private $availablePreviewTypes;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LogEntryTypes $logEntryTypes,
        array $components = [],
        array $data = [],
        array $availablePreviewTypes = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->logEntryTypes = $logEntryTypes;
        $this->availablePreviewTypes = $availablePreviewTypes;
    }

    public function prepareDataSource(array $dataSource)
    {
        $previewTypes = array_filter(array_keys($this->logEntryTypes->toArray()), function ($type) {
            return in_array($type, $this->availablePreviewTypes);
        });
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['details'] = [
                'href' => $this->context->getUrl(
                    'amaudit/actionslog/edit',
                    ['id' => $item['id']]
                ),
                'label' => __('View Details'),
                'hidden' => false,
            ];

            if (in_array($item['type'], $previewTypes)) {
                $item[$this->getData('name')]['preview'] = [
                    'callback' => [
                        'provider' => 'index = preview-modal',
                        'target' => 'getPreviewData',
                        'id' => $item['id'],
                    ],
                    'label' => __('Preview Details'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
