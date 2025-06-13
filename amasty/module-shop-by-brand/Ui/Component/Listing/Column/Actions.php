<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Ui\Component\Listing\Column;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = (int) $this->context->getFilterParam('scope') ?: 0;
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[OptionSettingInterface::ATTRIBUTE_CODE])) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'amasty_shopbybrand/slider/edit',
                            [
                                'attribute_code' => $item[OptionSettingInterface::ATTRIBUTE_CODE],
                                'option_id' => $item['option_id'],
                                'store' => $storeId
                            ]
                        ),
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
