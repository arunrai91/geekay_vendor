<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class TerminateAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->getData('config/indexField')])) {
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
                    $config = (array)$this->getData('config');
                    if ($config && isset($config['buttons'])) {
                        foreach ($config['buttons'] as $actionName => $button) {
                            $label = $button['itemLabel'];
                            $item[$this->getData('name')][$actionName] = [
                                'href' => $this->urlBuilder->getUrl(
                                    $button['urlPath'],
                                    [
                                        $urlEntityParamName => $item[$this->getData('config/indexField')]
                                    ]
                                ),
                                'label' => __($label)
                            ];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
